@extends('layouts.app')

@section('title', 'Tambah User - Panel Admin')
@section('header-title', 'Tambah Pengguna Baru')

@section('content')
<div class="max-w-xl bg-slate-900 rounded-xl shadow-sm border border-slate-800 p-6">
    <form action="{{ route('admin.user.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('name') <span class="text-rose-400 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('email') <span class="text-rose-400 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Password</label>
            <input type="password" name="password" required
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('password') <span class="text-rose-400 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Role / Hak Akses</label>
            <select name="role" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="peminjam">Peminjam</option>
                <option value="petugas">Petugas</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="mb-6">
            <label class="block text-slate-300 text-sm font-medium mb-2">No. HP (Opsional)</label>
            <input type="text" name="no_hp" value="{{ old('no_hp') }}"
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.user.index') }}"
                class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-lg shadow-indigo-500/20">Simpan</button>
        </div>
    </form>
</div>
@endsection