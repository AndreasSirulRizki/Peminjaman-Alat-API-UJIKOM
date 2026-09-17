@extends('layouts.app')

@section('title', 'Tambah Peminjaman - Panel Admin')
@section('header-title', 'Form Tambah Transaksi Peminjaman')

@section('content')
    <div class="max-w-2xl bg-slate-900 rounded-xl shadow-sm border border-slate-800 p-6">

        {{-- ============================================================ --}}
        {{-- BLOK ERROR SESSION (misal: stok tidak mencukupi)             --}}
        {{-- ============================================================ --}}
        @if(session('error'))
            <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 rounded-lg text-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- BLOK ERROR VALIDASI LARAVEL (ringkasan semua field)           --}}
        {{-- ============================================================ --}}
        @if($errors->any())
            <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-lg text-sm">
                <div class="flex items-center gap-2 mb-1 font-medium">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>Terjadi kesalahan:</span>
                </div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.peminjaman.store') }}" method="POST">
            @csrf

            {{-- ======================================================== --}}
            {{-- PILIH PEMINJAM                                            --}}
            {{-- ======================================================== --}}
            <div class="mb-4">
                <label class="block text-slate-300 text-sm font-semibold mb-2">Pilih Peminjam (User)</label>
                <select name="user_id" required
                    class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500
                           {{ $errors->has('user_id') ? 'border-rose-500/60' : '' }}">
                    <option value="">— Pilih User —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            {{-- ======================================================== --}}
            {{-- TANGGAL PINJAM & RENCANA KEMBALI                         --}}
            {{-- type="date" wajib agar Laravel dapat membandingkan        --}}
            {{-- tanggal dengan rule after_or_equal                        --}}
            {{-- ======================================================== --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Tanggal Pinjam</label>
                    <input type="date"
                           name="tgl_pinjam"
                           value="{{ old('tgl_pinjam', date('Y-m-d')) }}"
                           required
                           class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  [color-scheme:dark]
                                  {{ $errors->has('tgl_pinjam') ? 'border-rose-500/60' : '' }}">
                    @error('tgl_pinjam')
                        <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Rencana Tanggal Kembali</label>
                    <input type="date"
                           name="tgl_kembali_plan"
                           value="{{ old('tgl_kembali_plan', date('Y-m-d', strtotime('+3 days'))) }}"
                           required
                           class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  [color-scheme:dark]
                                  {{ $errors->has('tgl_kembali_plan') ? 'border-rose-500/60' : '' }}">
                    @error('tgl_kembali_plan')
                        <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- DAFTAR ALAT YANG DIPINJAM (DINAMIS)                      --}}
            {{-- min="1" DIHAPUS dari input jumlah[] agar validasi        --}}
            {{-- HTML5 tidak memblokir submit — biarkan Laravel yang       --}}
            {{-- menampilkan pesan error dalam bahasa Indonesia            --}}
            {{-- ======================================================== --}}
            <div class="mb-6">
                <label class="block text-slate-300 text-sm font-semibold mb-2">Daftar Alat yang Dipinjam</label>

                @error('alat_id')
                    <span class="text-rose-400 text-xs mb-2 block">{{ $message }}</span>
                @enderror

                <div id="alat-container" class="space-y-2">
                    <div class="alat-row">
                        <div class="flex items-center gap-2">
                            <select name="alat_id[]" required
                                class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Pilih Alat —</option>
                                @foreach($alats as $alat)
                                    <option value="{{ $alat->id }}">{{ $alat->nama_alat }} (Stok: {{ $alat->stok }})</option>
                                @endforeach
                            </select>
                            <input type="number"
                                   name="jumlah[]"
                                   value="1"
                                   placeholder="Jumlah"
                                   class="w-24 px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <button type="button" onclick="removeRow(this)"
                                class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 px-3 py-2 rounded-lg text-sm transition">✕</button>
                        </div>
                        {{-- Error per-baris jumlah (baris pertama = index 0) --}}
                        @error('jumlah.0')
                            <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                        @error('alat_id.0')
                            <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Render ulang baris tambahan jika ada old input (setelah validasi gagal) --}}
                    @if(old('alat_id'))
                        @foreach(old('alat_id') as $idx => $oldAlatId)
                            @if($idx === 0) @continue @endif
                            <div class="alat-row">
                                <div class="flex items-center gap-2">
                                    <select name="alat_id[]" required
                                        class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">— Pilih Alat —</option>
                                        @foreach($alats as $alat)
                                            <option value="{{ $alat->id }}" {{ $oldAlatId == $alat->id ? 'selected' : '' }}>
                                                {{ $alat->nama_alat }} (Stok: {{ $alat->stok }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number"
                                           name="jumlah[]"
                                           value="{{ old('jumlah')[$idx] ?? 1 }}"
                                           placeholder="Jumlah"
                                           class="w-24 px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <button type="button" onclick="removeRow(this)"
                                        class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 px-3 py-2 rounded-lg text-sm transition">✕</button>
                                </div>
                                @error("jumlah.$idx")
                                    <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                                @error("alat_id.$idx")
                                    <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        @endforeach
                    @endif
                </div>

                <button type="button" onclick="addRow()"
                    class="mt-3 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs font-semibold px-3 py-2 rounded-lg transition">
                    + Tambah Alat Lain
                </button>
            </div>

            {{-- ======================================================== --}}
            {{-- TOMBOL AKSI                                               --}}
            {{-- ======================================================== --}}
            <div class="flex justify-end space-x-2">
                <a href="{{ route('admin.peminjaman.index') }}"
                    class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
                <button type="submit"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-lg shadow-indigo-500/20">Simpan Peminjaman</button>
            </div>
        </form>
    </div>

    <script>
        // Template HTML untuk baris baru (tanpa min="1")
        function getAlatOptions() {
            // Ambil semua option dari select pertama yang sudah ada
            const firstSelect = document.querySelector('#alat-container .alat-row select');
            return firstSelect.innerHTML;
        }

        function addRow() {
            const container = document.getElementById('alat-container');
            const rowCount  = container.querySelectorAll('.alat-row').length;

            const newRow = document.createElement('div');
            newRow.classList.add('alat-row');
            newRow.innerHTML = `
                <div class="flex items-center gap-2">
                    <select name="alat_id[]" required
                        class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        ${getAlatOptions()}
                    </select>
                    <input type="number"
                           name="jumlah[]"
                           value="1"
                           placeholder="Jumlah"
                           class="w-24 px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="button" onclick="removeRow(this)"
                        class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 px-3 py-2 rounded-lg text-sm transition">✕</button>
                </div>
            `;

            // Reset select ke default setelah clone
            newRow.querySelector('select').value = '';

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
