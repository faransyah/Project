<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('unit');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        if ($request->unit && $request->unit !== 'Semua Unit') {
            $query->where('unit_id', $request->unit);
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'units' => Unit::select('id', 'name', 'alias', 'address')->get(),
            'filters' => $request->only(['search', 'unit']),
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $validated = $request->validate([
            'full_name'     => 'required|string|max:255',
            'username'      => 'required|string|max:50|unique:users',
            'email'         => 'required|email|max:100|unique:users',
            'password'      => 'required|string|min:3',
            'nip'           => 'nullable|string',
            'unit_id'       => 'required|exists:units,id',
            'role'          => 'required|in:Admin,User',
            'phone'         => 'nullable|string',
            'position_name' => 'nullable|string',
            'is_active'     => 'boolean',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048', 
        ]);

        // 2. Logika Upload Foto
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profile_photos', 'public');
            $validated['url_photo'] = $path;
        }

        // 3. Persiapan Data
        $validated['password'] = Hash::make($validated['password']);
        $validated['created_by'] = Auth::user()->full_name ?? 'System';

        // 4. Hapus key 'photo' agar tidak error saat create (karena kolom DB namanya url_photo)
        unset($validated['photo']);

        User::create($validated);

        return redirect()->back()->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => ['required', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'unit_id' => 'required|exists:units,id',
            'role' => 'required',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'phone' => 'nullable', 
            'nip' => 'nullable',
            'position_name' => 'nullable', 
            'is_active' => 'boolean'
        ]);

        // Proses Update Foto
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($user->url_photo) {
                Storage::disk('public')->delete($user->url_photo);
            }
            $path = $request->file('photo')->store('profile_photos', 'public');
            $validated['url_photo'] = $path;
        }

        // Update Password HANYA jika diisi
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']); // Jangan update password jika kosong
        }

        unset($validated['photo']); // Bersihkan request sebelum update ke DB

        $user->update($validated);

        return redirect()->back()->with('success', 'Data pegawai diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->url_photo) {
            Storage::disk('public')->delete($user->url_photo);
        }
        $user->delete();
        return redirect()->back()->with('success', 'Pegawai berhasil dihapus.');
    }
}