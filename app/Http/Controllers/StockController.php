<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Item;
use App\Models\Unit;
use App\Models\User; // <--- WAJIB IMPORT USER
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Data Stok Utama
        $stockQuery = Stock::with(['item.category', 'unit', 'user']);

        if ($request->search) {
            $stockQuery->whereHas('item', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->unit && $request->unit !== 'Semua Unit') {
            $stockQuery->where('unit_id', $request->unit);
        }

        // 2. Query History
        $historyQuery = StockHistory::with('unit')->latest();

        // 3. Render Halaman
        return Inertia::render('Stocks/Index', [
            'stocks' => $stockQuery->paginate(10, ['*'], 'stocks_page')->withQueryString(),
            'history' => $historyQuery->paginate(10, ['*'], 'history_page')->withQueryString(),
            
            // --- DATA UNTUK MODAL TAMBAH STOK ---
            'items' => Item::with('category')->get(), 
            
            // PENTING: Kirim data user BESERTA unitnya.
            // Ini supaya Vue bisa otomatis tahu unit si user.
            'userOptions' => User::with('unit')->whereNotNull('unit_id')->get(), 
            
            // Unit tetap dikirim jika mau dipakai filter tabel, tapi di Modal form gak wajib dipake manual
            'units' => Unit::all(), 

            'filters' => $request->only(['search', 'unit', 'history_search']),
        ]);
    }

    public function store(Request $request)
    {
        // Validasi: Unit ID TIDAK WAJIB VALIDASI INPUT (karena diambil otomatis dari User)
        // Kita validasi user_id aja.
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'user_id' => 'required|exists:users,id', // User Wajib
            'stock'   => 'required|integer|min:0',
            'stock_min' => 'required|integer|min:0',
            'price'   => 'required|numeric|min:0',
            'status'  => 'required|in:Active,Inactive',
        ]);

        // 1. AMBIL UNIT DARI USER
        $user = User::findOrFail($request->user_id);
        
        // Jaga-jaga user gapunya unit (meski di frontend udah difilter)
        if (!$user->unit_id) {
            return redirect()->back()->with('error', 'User yang dipilih tidak memiliki Unit Kerja!');
        }

        // 2. Cek Duplikat (Item sama, User sama)
        $exists = Stock::where('item_id', $request->item_id)
                        ->where('user_id', $request->user_id)
                        ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Stok barang ini sudah ada pada user tersebut!');
        }

        DB::transaction(function () use ($validated, $user) {
            // 3. Simpan Stok (Unit ID diambil dari $user->unit_id)
            Stock::create([
                'item_id' => $validated['item_id'],
                'user_id' => $validated['user_id'],
                'unit_id' => $user->unit_id, // <--- OTOMATIS DARI USER
                'stock'   => $validated['stock'],
                'stock_min' => $validated['stock_min'],
                'price'   => $validated['price'],
                'status'  => $validated['status'],
            ]);

            // 4. Catat History
            $item = Item::find($validated['item_id']);

            StockHistory::create([
                'item_id' => $validated['item_id'],
                'unit_id' => $user->unit_id, // <--- OTOMATIS DARI USER
                'type' => 'IN',
                'qty' => $validated['stock'],
                'item_name_snapshot' => $item->name,
                'actor' => Auth::user()->name ?? 'Admin',
                'note' => 'Stok Awal (Pemegang: ' . $user->name . ')',
            ]);
        });

        return redirect()->back()->with('success', 'Stok berhasil ditambahkan untuk ' . $user->name);
    }

    public function update(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'stock_min' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:Active,Inactive',
            'txType' => 'nullable|in:in,out',
            'txQty' => 'nullable|integer|min:1',
            'txNote' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $stock, $validated) {
            if ($request->filled('txType') && $request->filled('txQty')) {
                $qty = $request->txQty;
                $type = '';

                if ($request->txType === 'in') {
                    $stock->increment('stock', $qty);
                    $type = 'IN';
                } else {
                    if ($stock->stock < $qty) throw new \Exception('Stok tidak cukup!');
                    $stock->decrement('stock', $qty);
                    $type = 'OUT';
                }
                
                StockHistory::create([
                    'item_id' => $stock->item_id,
                    'unit_id' => $stock->unit_id,
                    'type' => $type,
                    'qty' => $qty,
                    'item_name_snapshot' => $stock->item->name,
                    'actor' => Auth::user()->name ?? 'Admin',
                    'note' => $request->txNote ?? 'Update Manual',
                ]);
            }

            $stock->update([
                'stock_min' => $validated['stock_min'],
                'price' => $validated['price'],
                'status' => $validated['status'],
            ]);
        });

        return redirect()->back()->with('success', 'Data stok diperbarui.');
    }
    
    public function destroy(Stock $stock)
    {
        $stock->delete();
        return redirect()->back()->with('success', 'Stok dihapus.');
    }
}