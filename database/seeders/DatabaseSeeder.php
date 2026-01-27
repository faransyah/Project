<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Item;
use App\Models\User;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\Approval;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. BERSIHKAN DATABASE
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Approval::truncate(); StockHistory::truncate(); Stock::truncate();
        User::truncate(); Item::truncate(); Unit::truncate(); Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- 1. CATEGORIES ---
        $cat_alat_tulis = Category::create(['name' => 'Alat Tulis']);
        $cat_kertas     = Category::create(['name' => 'Kertas & Dokumen']);
        $cat_tinta      = Category::create(['name' => 'Tinta & Toner']);
        $cat_perlengkapan = Category::create(['name' => 'Perlengkapan']);
        $cat_lain       = Category::create(['name' => 'Lain-lain']);

        // --- 2. UNITS ---
        $u_jatim = Unit::create(['alias' => 'UID Jatim', 'name' => 'PT PLN (Persero) UID Jawa Timur', 'address' => 'Surabaya', 'phone' => '031-12345', 'ref_id' => 'UID-001', 'parent_id' => 0]);
        $u_jabar = Unit::create(['alias' => 'UID Jabar', 'name' => 'PT PLN (Persero) UID Jawa Barat', 'address' => 'Bandung', 'phone' => '022-67890', 'ref_id' => 'UID-002', 'parent_id' => 0]);
        $u_pusat = Unit::create(['alias' => 'Kantor Pusat', 'name' => 'PT PLN (Persero) Kantor Pusat', 'address' => 'Jakarta Pusat', 'phone' => '021-123456', 'ref_id' => 'K-PUSAT', 'parent_id' => 0]);
        $u_sby_utara = Unit::create(['alias' => 'UP3 SBY Utara', 'name' => 'UP3 Surabaya Utara', 'address' => 'Surabaya', 'phone' => '031-5342123', 'ref_id' => 'UP3-001', 'parent_id' => $u_jatim->id]);

        // --- 3. ITEMS ---
        $atk_pensil = Item::create(['code' => 'ATK-001', 'name' => 'Pensil 2B Faber-Castell', 'category_id' => $cat_alat_tulis->id, 'uom' => 'Pcs', 'price' => 3500, 'min_stock' => 10]);
        $atk_kertas = Item::create(['code' => 'ATK-002', 'name' => 'Kertas A4 Sinar Dunia 80gr', 'category_id' => $cat_kertas->id, 'uom' => 'Rim', 'price' => 45000, 'min_stock' => 20]);
        $atk_tinta = Item::create(['code' => 'ATK-003', 'name' => 'Tinta Epson 003 Black', 'category_id' => $cat_tinta->id, 'uom' => 'Botol', 'price' => 85000, 'min_stock' => 5]);
        $atk_spidol = Item::create(['code' => 'ATK-005', 'name' => 'Spidol Snowman Boardmarker', 'category_id' => $cat_alat_tulis->id, 'uom' => 'Pcs', 'price' => 8500, 'min_stock' => 20]);

        // --- 4. USERS ---
        User::create(['full_name' => 'Budi Santoso', 'username' => 'budi.santoso', 'email' => 'admin@pln.co.id', 'password' => Hash::make('password'), 'role' => 'Admin', 'unit_id' => $u_pusat->id, 'nip' => '19850101', 'position_name' => 'Manager Logistik']);
        User::create(['full_name' => 'Siti Aminah', 'username' => 'siti.aminah', 'email' => 'siti@pln.co.id', 'password' => Hash::make('123'), 'role' => 'User', 'unit_id' => $u_jatim->id, 'nip' => '19900202', 'position_name' => 'Staff Gudang']);
        User::create(['full_name' => 'Rudi Hartono', 'username' => 'rudi.hartono', 'email' => 'rudi@pln.co.id', 'password' => Hash::make('123'), 'role' => 'User', 'unit_id' => $u_jabar->id, 'nip' => '19880303', 'position_name' => 'Supervisor']);

        // --- 5. STOCKS (FIXED: stock_min) ---
        
        Stock::create([
            'item_id' => $atk_kertas->id, 
            'unit_id' => $u_jatim->id, 
            'stock' => 500, 
            'stock_min' => 50, // <--- Perbaikan disini
            'price' => 45000
        ]);
        
        Stock::create([
            'item_id' => $atk_pensil->id, 
            'unit_id' => $u_jatim->id, 
            'stock' => 150, 
            'stock_min' => 20, // <--- Perbaikan disini
            'price' => 3500
        ]);
        
        Stock::create([
            'item_id' => $atk_tinta->id, 
            'unit_id' => $u_jabar->id, 
            'stock' => 3, 
            'stock_min' => 5, // <--- Perbaikan disini (Tinta Kritis)
            'price' => 85000
        ]);

        Stock::create([
            'item_id' => $atk_spidol->id, 
            'unit_id' => $u_sby_utara->id, 
            'stock' => 0, 
            'stock_min' => 10, // <--- Perbaikan disini
            'price' => 8500
        ]);

        // --- 6. HISTORY ---
        StockHistory::create(['type' => 'IN', 'item_id' => $atk_kertas->id, 'unit_id' => $u_jatim->id, 'item_name_snapshot' => $atk_kertas->name, 'qty' => 500, 'actor' => 'System', 'created_at' => now()->subDays(5)]);
        StockHistory::create(['type' => 'OUT', 'item_id' => $atk_tinta->id, 'unit_id' => $u_jabar->id, 'item_name_snapshot' => $atk_tinta->name, 'qty' => 10, 'actor' => 'Rudi Hartono', 'created_at' => now()->subDays(1)]);

        // --- 7. APPROVALS ---
        Approval::create(['user_name' => 'Rudi Hartono', 'unit_id' => $u_jabar->id, 'item_id' => $atk_tinta->id, 'item_name_snapshot' => $atk_tinta->name, 'item_count' => 10, 'value_estimate' => 'Rp 850.000', 'status' => 'Pending']);
        Approval::create(['user_name' => 'Siti Aminah', 'unit_id' => $u_jatim->id, 'item_id' => $atk_spidol->id, 'item_name_snapshot' => $atk_spidol->name, 'item_count' => 50, 'value_estimate' => 'Rp 425.000', 'status' => 'Pending']);
    }
}