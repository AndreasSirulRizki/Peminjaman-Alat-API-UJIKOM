@extends('layouts.app')

@section('title', 'Tambah Alat - Panel Admin')
@section('header-title', 'Tambah Alat Baru')

@section('content')
<div class="max-w-xl bg-slate-900 rounded-xl shadow-sm border border-slate-800 p-6">
    <form action="{{ route('admin.alat.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Kategori</label>
            <select name="kategori_id" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Nama Alat</label>
            <input type="text" name="nama_alat" value="{{ old('nama_alat') }}" required
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Stok</label>
            <input type="number" name="stok" value="{{ old('stok', 0) }}" required min="0"
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Status Kondisi</label>
            <input type="text" name="status_kondisi" value="{{ old('status_kondisi', 'Baik') }}" required
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="mb-6">
            <label class="block text-slate-300 text-sm font-medium mb-2">Deskripsi</label>
            <textarea name="deskripsi" rows="3"
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('deskripsi') }}</textarea>
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.alat.index') }}"
                class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-lg shadow-indigo-500/20">Simpan</button>
        </div>
    </form>
</div>
@endsection