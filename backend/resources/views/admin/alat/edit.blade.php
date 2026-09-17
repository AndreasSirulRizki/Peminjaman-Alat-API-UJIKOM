@extends('layouts.app')

@section('title', 'Edit Alat - Panel Admin')
@section('header-title', 'Edit Data Alat')

@section('content')
<div class="max-w-xl bg-slate-900 rounded-xl shadow-sm border border-slate-800 p-6">
    <form action="{{ route('admin.alat.update', $alat->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Kategori</label>
            <select name="kategori_id" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" {{ $alat->kategori_id == $kategori->id ? 'selected' : '' }}>{{ $kategori->nama_kategori }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Nama Alat</label>
            <input type="text" name="nama_alat" value="{{ old('nama_alat', $alat->nama_alat) }}" required
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('nama_alat') <span class="text-rose-400 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Stok</label>
            <input type="number" name="stok" value="{{ old('stok', $alat->stok) }}" required min="0"
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-medium mb-2">Status Kondisi</label>
            <input type="text" name="status_kondisi" value="{{ old('status_kondisi', $alat->status_kondisi) }}" required
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="mb-6">
            <label class="block text-slate-300 text-sm font-medium mb-2">Deskripsi</label>
            <textarea name="deskripsi" rows="3"
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('deskripsi', $alat->deskripsi) }}</textarea>
        </div>

        <div class="mb-6">
            <label class="block text-slate-300 text-sm font-medium mb-2">
                Foto Alat
                <span class="text-xs text-slate-500 font-normal">(kosongkan jika tidak ingin mengubah foto)</span>
            </label>

            {{-- Preview foto saat ini --}}
            @if($alat->gambar)
                <div class="mb-3 flex items-center gap-3">
                    <img id="preview-edit"
                         src="{{ asset('foto/' . rawurlencode($alat->gambar)) }}"
                         alt="{{ $alat->nama_alat }}"
                         class="w-24 h-24 object-cover rounded-lg border border-slate-700"
                         onerror="this.onerror=null; this.src=''; this.classList.add('hidden'); document.getElementById('no-foto-edit').classList.remove('hidden');">
                    <div id="no-foto-edit" class="w-24 h-24 bg-slate-800 rounded-lg border border-slate-700 flex items-center justify-center hidden">
                        <i class="fas fa-image text-slate-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Foto saat ini:</p>
                        <p class="text-xs text-slate-300 mt-0.5">{{ $alat->gambar }}</p>
                    </div>
                </div>
            @else
                <div class="mb-3 flex items-center gap-3">
                    <div class="w-24 h-24 bg-slate-800 rounded-lg border border-slate-700 flex items-center justify-center">
                        <i class="fas fa-image text-slate-500 text-xl"></i>
                    </div>
                    <p class="text-xs text-slate-500">Belum ada foto</p>
                </div>
                <img id="preview-edit" src="" alt="Preview" class="w-24 h-24 object-cover rounded-lg border border-slate-700 hidden mb-3">
            @endif

            <input type="file" name="gambar" id="gambar-edit" accept="image/*"
                class="block w-full text-sm text-slate-400
                       file:mr-4 file:py-2 file:px-4
                       file:rounded-lg file:border-0
                       file:text-sm file:font-semibold
                       file:bg-slate-700 file:text-slate-300
                       hover:file:bg-slate-600 cursor-pointer
                       bg-slate-800 border border-slate-700 rounded-lg"
                onchange="previewGambarEdit(this)">
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.alat.index') }}"
                class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-lg shadow-indigo-500/20">Perbarui</button>
        </div>
    </form>
</div>

<script>
function previewGambarEdit(input) {
    const img = document.getElementById('preview-edit');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            img.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection