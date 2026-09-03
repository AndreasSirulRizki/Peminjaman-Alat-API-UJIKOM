@extends('layouts.app')

@section('title', 'Edit Kategori - Panel Admin')
@section('header-title', 'Edit Kategori Alat')

@section('content')
<div class="max-w-xl bg-slate-900 rounded-xl shadow-sm border border-slate-800 p-6">
    <form action="{{ route('admin.kategori.update', $kategori->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Nama Kategori</label>
            <input type="text" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}" required
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('nama_kategori') <span class="text-rose-400 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.kategori.index') }}"
                class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-lg shadow-indigo-500/20">Perbarui</button>
        </div>
    </form>
</div>
@endsection