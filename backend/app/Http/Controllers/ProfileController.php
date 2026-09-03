<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Tentukan nama layout dan route dashboard berdasarkan role user.
     * Layout:
     *   - admin    → layouts.app        (sidebar admin)
     *   - petugas  → layouts.app-simple (sidebar petugas)
     *   - peminjam → layouts.peminjam   (sidebar peminjam)
     */
    private function layoutForRole(string $role): string
    {
        return match ($role) {
            'admin'    => 'layouts.app',
            'petugas'  => 'layouts.app-simple',
            'peminjam' => 'layouts.peminjam',
            default    => 'layouts.peminjam',
        };
    }

    /**
     * Route dashboard masing-masing role — dipakai untuk redirect setelah update.
     */
    private function dashboardRouteForRole(string $role): string
    {
        return match ($role) {
            'admin'    => 'admin.dashboard',
            'petugas'  => 'petugas.peminjaman.index',
            'peminjam' => 'peminjam.katalog',
            default    => 'login',
        };
    }

    /**
     * Tampilkan form edit profil user yang sedang login.
     * Layout disesuaikan dengan role agar sidebar/navbar tampil benar.
     */
    public function edit()
    {
        $user   = auth()->user();
        $layout = $this->layoutForRole($user->role);

        return view('profile.edit', compact('user', 'layout'));
    }

    /**
     * Simpan perubahan profil user yang sedang login.
     * - Email tidak bisa diubah (read-only).
     * - Password baru opsional (kosongkan jika tidak ingin ganti).
     * - Foto profil opsional (simpan ke storage/app/public/foto_profile/).
     * - Setelah berhasil, redirect ke dashboard sesuai role.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'         => 'required|string|max:255',
            'no_hp'        => 'nullable|string|max:20',
            'alamat'       => 'nullable|string|max:500',
            'foto_profile' => 'nullable|image|max:2048',
            'password'     => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'name'   => $request->name,
            'no_hp'  => $request->no_hp,
            'alamat' => $request->alamat,
        ];

        // Proses upload foto profil baru jika ada
        if ($request->hasFile('foto_profile')) {
            // Hapus foto lama jika sudah ada
            if ($user->foto_profile && Storage::disk('public')->exists($user->foto_profile)) {
                Storage::disk('public')->delete($user->foto_profile);
            }

            // Simpan foto baru ke storage/app/public/foto_profile/
            $path = $request->file('foto_profile')->store('foto_profile', 'public');
            $data['foto_profile'] = $path;
        }

        // Update password jika diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Redirect ke halaman Edit Profil kembali (dengan flash sukses),
        // route profile.edit akan otomatis memakai layout yang benar sesuai role.
        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
