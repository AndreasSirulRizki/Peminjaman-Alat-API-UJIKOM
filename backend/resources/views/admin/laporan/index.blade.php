@extends('layouts.app')

@section('title', 'Laporan Peminjaman - Panel Admin')
@section('header-title', 'Generate Laporan Peminjaman')

@section('content')

<div class="max-w-3xl bg-slate-900 rounded-lg shadow-sm border border-slate-700/50 p-6">

    @if ($errors->any())
        <div class="mb-4 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg shadow-sm text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.laporan.generate') }}" method="POST" target="_blank">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Tanggal Awal</label>
                <input type="text" name="start_date" value="{{ old('start_date') }}"
                       placeholder="Pilih tanggal awal..."
                       class="datepicker-input w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-white">
            </div>
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Tanggal Akhir</label>
                <input type="text" name="end_date" value="{{ old('end_date') }}"
                       placeholder="Pilih tanggal akhir..."
                       class="datepicker-input w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-white">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-slate-300 text-sm font-semibold mb-2">Filter Status</label>
            <select name="status"
                    class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-white">
                <option value="">-- Semua Status --</option>
                <option value="diajukan" {{ old('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                <option value="dipinjam" {{ old('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="dikembalikan" {{ old('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                <option value="telat" {{ old('status') == 'telat' ? 'selected' : '' }}>Telat</option>
            </select>
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.dashboard') }}"
               class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                Batal
            </a>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                <i class="fas fa-file-pdf mr-2"></i> Generate PDF
            </button>
        </div>
    </form>
</div>

@endsection