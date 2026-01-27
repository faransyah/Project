<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // <--- WAJIB: Untuk hapus/simpan file
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with('category');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        $items = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Items/Index', [
            'items' => $items,
            'categories' => Category::all(), // Data untuk dropdown kategori
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'code'        => 'required|string|unique:items,code',
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'uom'         => 'required|string',
            'price'       => 'nullable|numeric|min:0',
            'min_stock'   => 'nullable|integer|min:0',
            'max_stock'   => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|in:Active,Inactive',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Validasi file gambar
        ]);

        // 2. Logika Upload Foto
        if ($request->hasFile('photo')) {
            // Simpan ke folder 'public/item_photos'
            $path = $request->file('photo')->store('item_photos', 'public');
            $validated['url_photo'] = $path;
        }

        // 3. Tambahan Data
        $validated['created_by'] = Auth::user()->full_name ?? 'System';
        
        // Hapus key 'photo' dari array karena di database namanya 'url_photo'
        unset($validated['photo']); 

        // 4. Simpan ke Database
        Item::create($validated);

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update(Request $request, Item $item)
    {
        // 1. Validasi Input Update
        $validated = $request->validate([
            'code'        => ['required', 'string', Rule::unique('items')->ignore($item->id)],
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'uom'         => 'required|string',
            'price'       => 'nullable|numeric|min:0',
            'min_stock'   => 'nullable|integer|min:0',
            'max_stock'   => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|in:Active,Inactive',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // 2. Logika Update Foto
        if ($request->hasFile('photo')) {
            // Cek apakah barang ini punya foto lama? Jika ya, HAPUS dulu biar gak nyampah.
            if ($item->url_photo) {
                Storage::disk('public')->delete($item->url_photo);
            }

            // Upload foto baru
            $path = $request->file('photo')->store('item_photos', 'public');
            $validated['url_photo'] = $path;
        }

        $validated['updated_by'] = Auth::user()->full_name ?? 'Admin';
        unset($validated['photo']);

        // 3. Update Database
        $item->update($validated);

        return redirect()->back()->with('success', 'Data barang diperbarui.');
    }

    public function destroy(Item $item)
    {
        // 1. Hapus file foto fisik jika ada
        if ($item->url_photo) {
            Storage::disk('public')->delete($item->url_photo);
        }

        // 2. Hapus data dari database
        $item->delete();
        
        return redirect()->back()->with('success', 'Barang berhasil dihapus.');
    }
}