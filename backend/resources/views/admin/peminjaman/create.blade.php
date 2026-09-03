@extends('layouts.app')

@section('title', 'Tambah Peminjaman - Panel Admin')
@section('header-title', 'Form Tambah Transaksi Peminjaman')

@section('content')
    <div class="max-w-2xl bg-slate-900 rounded-xl shadow-sm border border-slate-800 p-6">
        @if(session('error'))
            <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.peminjaman.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-slate-300 text-sm font-semibold mb-2">Pilih Peminjam (User)</label>
                <select name="user_id" required
                    class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Pilih User —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Tanggal Pinjam</label>
                    <input type="text" name="tgl_pinjam"
                           value="{{ old('tgl_pinjam', date('Y-m-d')) }}"
                           placeholder="Pilih tanggal pinjam..." required
                           class="datepicker-input w-full px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Rencana Tanggal Kembali</label>
                    <input type="text" name="tgl_kembali_plan"
                           value="{{ old('tgl_kembali_plan', date('Y-m-d', strtotime('+3 days'))) }}"
                           placeholder="Pilih rencana kembali..." required
                           class="datepicker-input w-full px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-slate-300 text-sm font-semibold mb-2">Daftar Alat yang Dipinjam</label>
                <div id="alat-container" class="space-y-2">
                    <div class="flex items-center gap-2 alat-row">
                        <select name="alat_id[]" required class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Pilih Alat —</option>
                            @foreach($alats as $alat)
                                <option value="{{ $alat->id }}">{{ $alat->nama_alat }} (Stok: {{ $alat->stok }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="jumlah[]" value="1" min="1" placeholder="Jumlah"
                            class="w-24 px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button" onclick="removeRow(this)"
                            class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 px-3 py-2 rounded-lg text-sm transition">✕</button>
                    </div>
                </div>
                <button type="button" onclick="addRow()"
                    class="mt-3 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs font-semibold px-3 py-2 rounded-lg transition">
                    + Tambah Alat Lain
                </button>
            </div>

            <div class="flex justify-end space-x-2">
                <a href="{{ route('admin.peminjaman.index') }}"
                    class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
                <button type="submit"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-lg shadow-indigo-500/20">Simpan Peminjaman</button>
            </div>
        </form>
    </div>

    <script>
        function addRow() {
            const container = document.getElementById('alat-container');
            const firstRow = container.querySelector('.alat-row');
            const newRow = firstRow.cloneNode(true);
            newRow.querySelector('select').value = '';
            newRow.querySelector('input').value = '1';
            container.appendChild(newRow);
        }

        function removeRow(button) {
            const rows = document.querySelectorAll('.alat-row');
            if (rows.length > 1) {
                button.closest('.alat-row').remove();
            } else {
                alert('Minimal harus ada 1 alat yang dipilih.');
            }
        }
    </script>
@endsection