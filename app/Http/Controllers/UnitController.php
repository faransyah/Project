<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::query();

        // Pencarian
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('alias', 'like', "%{$request->search}%")
                  ->orWhere('ref_id', 'like', "%{$request->search}%");
            });
        }

        // Pagination
        $units = $query->orderBy('alias')->paginate(10)->withQueryString();

        return Inertia::render('Units/Index', [
            'units' => $units,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ref_id' => 'required|string|max:50|unique:units,ref_id',
            'alias' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string',
            'parent_id' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::user()->full_name ?? 'System';

        Unit::create($validated);

        return redirect()->back()->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'ref_id' => 'required|string|max:50|unique:units,ref_id,' . $unit->id,
            'alias' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string',
            'parent_id' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['updated_by'] = Auth::user()->full_name ?? 'Admin';

        $unit->update($validated);

        return redirect()->back()->with('success', 'Data unit diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        // Cek apakah unit dipakai di User atau Stok
        if ($unit->users()->exists() || $unit->stocks()->exists()) {
            return redirect()->back()->with('error', 'Gagal hapus. Unit ini masih memiliki User atau Stok aktif.');
        }

        $unit->delete();
        return redirect()->back()->with('success', 'Unit berhasil dihapus.');
    }
}