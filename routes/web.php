<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockRequestController;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\Category;
use App\Models\Unit; 
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // KUNCI PERBAIKAN: Route 'dashboard' utama sebagai jembatan
    // Agar menu "Beranda" di Vue tidak error saat diklik oleh User maupun Admin
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('user.dashboard');
    })->name('dashboard');

    // ====================================================
    // ZONA ADMIN
    // ====================================================
    Route::middleware(['role:admin'])->group(function () {
        // Ubah nama rute dashboard admin agar tidak bentrok
        Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        
        Route::post('/approvals/{approval}/approve', [DashboardController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{approval}/reject', [DashboardController::class, 'reject'])->name('approvals.reject');
        
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
        Route::resource('units', UnitController::class)->except(['create', 'edit', 'show']);
        Route::resource('items', ItemController::class)->except(['create', 'edit', 'show']);
        Route::resource('stocks', StockController::class)->except(['create', 'edit', 'show']);
    });

    // ====================================================
    // ZONA USER
    // ====================================================
    Route::middleware(['role:user'])->group(function () {
        
        Route::get('/user/dashboard', function () {
            $user = Auth::user();

            $items = Item::select('items.*', 'categories.name as category_name')
                ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
                ->where('items.status', 'Active')
                ->get()
                ->map(function($item) use ($user) {
                    $stock = Stock::where('item_id', $item->id)
                                  ->where('unit_id', $user->unit_id)
                                  ->first();
                    $item->unit_stock = $stock ? $stock->stock : 0; 
                    return $item;
                });

            $history = StockRequest::with(['item'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
            
            $activeUnits = Unit::all(); 

            return Inertia::render('Users/Dashboard', [
                'items'       => $items,
                'categories'  => Category::all(),
                'history'     => $history,
                'auth_user'   => $user->load('unit'), 
                'activeUnits' => $activeUnits, 
            ]);
        })->name('user.dashboard');

        Route::post('/stock-requests', [StockRequestController::class, 'store'])->name('stock-requests.store');
    });

    // ====================================================
    // ZONA PROFILE (Akses Semua Role)
    // ====================================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';