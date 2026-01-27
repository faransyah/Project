<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. AMBIL DATA REQUEST (PENDING)
        $rawPending = StockRequest::with(['user', 'item', 'unit'])
            ->whereIn('status', ['pending', 'Pending']) 
            ->latest()
            ->get();

        $pendingApprovals = $rawPending->map(function ($req) {
            // --- LOGIKA NAMA USER (BIAR GAK MUNCUL 'USER HAPUS') ---
            $userName = '-';
            if ($req->user) {
                // Cek kolom 'name', kalau kosong cek 'full_name'
                $userName = $req->user->name ?? $req->user->full_name ?? $req->user->username ?? 'Tanpa Nama';
            }

            return [
                'id'           => $req->id,
                'user'         => $userName, // <--- Sudah diperbaiki
                'user_initial' => substr($userName, 0, 1),
                'itemName'     => $req->item->name ?? 'Item Hapus',
                'itemCount'    => $req->quantity,
                // Cek nama unit (name atau alias)
                'unit'         => $req->unit->name ?? $req->unit->alias ?? ($req->user->unit->name ?? '-'), 
                'created_at'   => $req->created_at->toISOString(),
            ];
        });

        // 2. STATISTIK REAL
        $stats = [
            'activeUnits'   => User::whereNotNull('unit_id')->count(),
            'totalATK'      => Item::count(),
            'totalStock'    => Stock::sum('stock'),
            'lowStockCount' => Stock::whereColumn('stock', '<=', 'stock_min')->count(),
        ];

        // 3. LOW STOCK ITEMS
        $lowStockItems = Stock::with(['item', 'unit'])
            ->whereColumn('stock', '<=', 'stock_min')
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get()
            ->map(function ($s) {
                return [
                    'id'    => $s->id,
                    'name'  => $s->item->name ?? 'Unknown',
                    'unit'  => $s->unit->name ?? 'Gudang',
                    'stock' => $s->stock,
                ];
            });

        // 4. CHART KATEGORI
        $categoryStats = DB::table('stocks')
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(stocks.stock) as value'))
            ->groupBy('categories.name')
            ->get();

        // 5. RIWAYAT TERBARU
        $recentActivity = StockHistory::with(['item', 'unit'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($log) {
                return [
                    'id'         => $log->id,
                    'type'       => $log->type,
                    'actor'      => $log->actor,
                    'qty'        => $log->qty,
                    'itemName'   => $log->item_name_snapshot ?? 'Item',
                    'created_at' => $log->created_at->toISOString()
                ];
            });

        return Inertia::render('Dashboard', [ 
            'pendingApprovals' => $pendingApprovals,
            'stats'            => $stats,
            'lowStockItems'    => $lowStockItems,
            'categoryStats'    => $categoryStats,
            'recentActivity'   => $recentActivity,
        ]);
    }

    public function approve($id)
    {
        try {
            DB::transaction(function () use ($id) {
                // 1. Ambil Data Request
                $req = StockRequest::with(['user', 'unit'])->findOrFail($id);

                if (strtolower($req->status) !== 'pending') {
                    throw new \Exception("Permintaan sudah diproses.");
                }

                // 2. Tentukan Unit ID
                $targetUnitId = $req->unit_id ?? ($req->user->unit_id ?? 1);

                // --- PERBAIKAN HARGA (Agar tidak Rp 0) ---
                $masterItem = Item::find($req->item_id);
                $fixPrice = $masterItem ? $masterItem->price : 0;

                // 3. Cari Stok atau Buat Baru
                $stock = Stock::firstOrNew([
                    'item_id' => $req->item_id,
                    'user_id' => $req->user_id, // Stok User Spesifik
                    'unit_id' => $targetUnitId,
                ]);

                // Set Data Stok (Termasuk Harga!)
                $stock->price = $fixPrice; // <--- INI PENTING
                $stock->stock_min = $stock->stock_min ?? 5;
                $stock->status = 'Active';
                
                // Tambah Stok
                $stock->stock = ($stock->stock ?? 0) + $req->quantity;
                $stock->save();

                // 4. Update Status Request
                $req->update([
                    'status'      => 'Approved',
                    // 'approved_by' => Auth::id(), // Aktifkan jika kolom ada
                    // 'approved_at' => now(),      // Aktifkan jika kolom ada
                    'note'        => 'Auto-approved by System'
                ]);

                // 5. Catat History
                StockHistory::create([
                    'item_id' => $req->item_id,
                    'unit_id' => $targetUnitId,
                    'type'    => 'IN',
                    'qty'     => $req->quantity,
                    'price'   => $fixPrice, // Simpan harga di history
                    'actor'   => Auth::user()->name ?? 'Admin',
                    'note'    => 'Req #' . $req->code,
                    'item_name_snapshot' => $req->item->name ?? 'Item'
                ]);
            });

            return redirect()->back()->with('success', 'Stok berhasil ditambahkan ke User!');

        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject($id)
    {
        try {
            $req = StockRequest::findOrFail($id);
            if (strtolower($req->status) !== 'pending') throw new \Exception("Sudah diproses.");
            
            $req->update([
                'status' => 'Rejected', 
                'note' => 'Ditolak Admin',
                // 'approved_at' => now() // Aktifkan jika kolom ada
            ]);
            return redirect()->back()->with('success', 'Permintaan ditolak.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}