<?php

namespace App\Http\Controllers;

use App\Models\StockRequest;
use App\Models\Item;
use App\Models\Stock;
use App\Models\Category;
use App\Models\StockHistory; // Pastikan Model ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockRequestController extends Controller
{
    /**
     * Menampilkan Halaman Request (Katalog + Riwayat)
     */
    public function create()
    {
        $user = Auth::user();

        // 1. Ambil Katalog Barang + Sisa Stok di Unit User
        $items = Item::select('items.*', 'categories.name as category_name')
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->where('items.status', 'Active')
            ->get()
            ->map(function($item) use ($user) {
                // Cari stok barang ini di unit user
                $stock = Stock::where('item_id', $item->id)
                              ->where('unit_id', $user->unit_id)
                              ->first();
                
                // Inject field 'unit_stock' agar Vue tahu sisanya berapa
                $item->unit_stock = $stock ? $stock->stock : 0; 
                return $item;
            });

        // 2. Ambil Riwayat Request User
        $history = StockRequest::with(['item'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        // Render ke Dashboard User
        // Sesuaikan path 'Users/Dashboard' dengan struktur folder View kakak
        return Inertia::render('Users/Dashboard', [
            'items'      => $items,
            'categories' => Category::all(),
            'history'    => $history,
            'auth_user'  => $user->load('unit'),
        ]);
    }

    /**
     * Menyimpan Request (Bisa Request Biasa atau Pemakaian Langsung)
     */
    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'description'       => 'nullable|string',
            'details'           => 'required|array|min:1',
            'details.*.item_id' => 'required|exists:items,id',
            'details.*.qty'     => 'required|integer|min:1',
        ]);

        DB::transaction(function() use ($request) {
            $user = Auth::user();
            // Buat Kode Batch Unik: REQ-{USERID}-{TIMESTAMP}
            $code = 'REQ-' . $user->id . '-' . now()->format('ymdHis');
            
            // Cek apakah ini PEMAKAIAN LANGSUNG (dari tombol 'Pakai' di Vue)
            $isConsume = str_contains($request->description, 'PEMAKAIAN LANGSUNG');

            foreach ($request->details as $detail) {
                
                // --- 1. LOGIKA CARI HARGA (FIX HARGA 0) ---
                $stockData = Stock::where('item_id', $detail['item_id'])
                                  ->where('unit_id', $user->unit_id)
                                  ->first();
                
                $currentPrice = 0;

                // Prioritas 1: Ambil harga dari Stok Unit (jika ada harganya)
                if ($stockData && $stockData->price > 0) {
                    $currentPrice = $stockData->price;
                } 
                // Prioritas 2: Jika stok kosong/harganya 0, AMBIL DARI MASTER BARANG
                else {
                    $masterItem = Item::find($detail['item_id']);
                    $currentPrice = $masterItem ? $masterItem->price : 0;
                }

                $status = 'Pending';

                // --- 2. LOGIKA PEMAKAIAN LANGSUNG (Consume) ---
                if ($isConsume) {
                   // Pastikan stok ada dan cukup
                   if ($stockData && $stockData->stock >= $detail['qty']) {
                       // Kurangi Stok
                       $stockData->decrement('stock', $detail['qty']);
                       
                       // Catat di History (OUT)
                       StockHistory::create([
                           'item_id' => $detail['item_id'],
                           'unit_id' => $user->unit_id,
                           'type'    => 'OUT',
                           'qty'     => $detail['qty'],
                           'price'   => $currentPrice, // Simpan harga
                           'item_name_snapshot' => $stockData->item->name ?? 'Unknown',
                           'actor'   => $user->name,
                           'note'    => 'Pemakaian: ' . $request->description,
                       ]);
                       
                       // Status langsung Approved karena barang sudah dipotong
                       $status = 'Approved';
                   } else {
                       // Security Check: Jika stok dimanipulasi/habis tiba-tiba
                       throw new \Exception("Gagal: Stok tidak cukup untuk item ID " . $detail['item_id']);
                   }
                }

                // --- 3. SIMPAN DATA REQUEST KE DATABASE ---
                StockRequest::create([
                    'code'        => $code,
                    'user_id'     => $user->id,
                    'unit_id'     => $user->unit_id,
                    'item_id'     => $detail['item_id'],
                    'quantity'    => $detail['qty'],
                    'price'       => $currentPrice, // HARGA TERSIMPAN DISINI
                    'status'      => $status,
                    'note'        => ($request->description ? "Utama: " . $request->description . ". " : "") . ($detail['notes'] ?? ''), 
                ]);
            }
        });

        return redirect()->back()->with('success', 'Transaksi berhasil diproses!');
    }
}