@extends('layouts.app-simple')

@section('title', 'Kelola Peminjaman - Petugas')
@section('header-title', 'Kelola Peminjaman')

@section('content')

    {{-- ============================================================ --}}
    {{-- FLASH MESSAGES                                               --}}
    {{-- ============================================================ --}}
    @if(session('success'))
        <div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Error validasi dari modal tolak (redirect back dengan error bag) --}}
    @if($errors->has('alasan_tolak'))
        <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-sm flex items-start gap-2">
            <i class="fas fa-circle-exclamation mt-0.5 flex-shrink-0"></i>
            <span>{{ $errors->first('alasan_tolak') }}</span>
        </div>
    @endif

    <h3 class="text-lg font-bold text-white mb-4">Daftar Pengajuan & Transaksi Peminjaman</h3>

    <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3 px-5 border-b border-slate-800">Peminjam</th>
                        <th class="py-3 px-5 border-b border-slate-800">Tanggal Pinjam</th>
                        <th class="py-3 px-5 border-b border-slate-800">Rencana Kembali</th>
                        <th class="py-3 px-5 border-b border-slate-800">Status</th>
                        <th class="py-3 px-5 border-b border-slate-800">Alat yang Dipinjam</th>
                        <th class="py-3 px-5 border-b border-slate-800">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    @forelse($peminjamans as $item)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-5 border-b border-slate-800 font-medium text-white">
                                {{ $item->user->name ?? '-' }}
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800 text-slate-400">
                                {{ $item->tgl_pinjam }}
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800 text-slate-400">
                                {{ $item->tgl_kembali_plan }}
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full border
                                    @if($item->status == 'diajukan')     bg-amber-500/10   text-amber-400   border-amber-500/20
                                    @elseif($item->status == 'dipinjam') bg-blue-500/10    text-blue-400    border-blue-500/20
                                    @elseif($item->status == 'selesai')  bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                    @else                                bg-rose-500/10    text-rose-400    border-rose-500/20
                                    @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800">
                                <ul class="space-y-2">
                                    @foreach($item->detailPinjam as $detail)
                                        <li class="flex items-center gap-2">
                                            @if($detail->alat && $detail->alat->gambar)
                                                <img src="{{ asset('foto/' . $detail->alat->gambar) }}"
                                                     alt="{{ $detail->alat->nama_alat }}"
                                                     class="w-9 h-9 object-cover rounded-md border border-slate-700 flex-shrink-0"
                                                     onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2236%22 height=%2236%22%3E%3Crect width=%2236%22 height=%2236%22 rx=%226%22 fill=%22%231e293b%22 stroke=%22%23334155%22/%3E%3C/svg%3E';">
                                            @else
                                                <div class="w-9 h-9 bg-slate-800 rounded-md border border-slate-700 flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-image text-slate-600 text-xs"></i>
                                                </div>
                                            @endif
                                            <span class="text-slate-300">{{ $detail->alat->nama_alat ?? 'Alat' }}
                                                <span class="text-xs text-slate-500">({{ $detail->jumlah }} pcs)</span>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800">
                                @if($item->status == 'diajukan')
                                    {{-- TOMBOL SETUJUI --}}
                                    <form action="{{ route('petugas.peminjaman.setujui', $item->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit"
                                            class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400
                                                   border border-emerald-500/20 text-xs font-semibold
                                                   px-3 py-1.5 rounded-lg transition">
                                            Setujui
                                        </button>
                                    </form>

                                    {{-- TOMBOL TOLAK — buka modal, kirim ID peminjaman --}}
                                    <button type="button"
                                            onclick="openModalTolak({{ $item->id }}, '{{ addslashes($item->user->name ?? 'Peminjam') }}')"
                                            class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400
                                                   border border-rose-500/20 text-xs font-semibold
                                                   px-3 py-1.5 rounded-lg transition ml-1">
                                        Tolak
                                    </button>

                                @elseif($item->status == 'dipinjam')
                                    {{-- TOMBOL TERIMA KEMBALI --}}
                                    <form action="{{ route('petugas.pengembalian.proses', $item->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="kondisi_kembali" value="Baik">
                                        <input type="hidden" name="denda" value="0">
                                        <button type="submit"
                                            class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-400
                                                   border border-amber-500/20 text-xs font-semibold
                                                   px-3 py-1.5 rounded-lg transition"
                                            onclick="return confirm('Proses pengembalian alat ini?')">
                                            Terima Kembali
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-500 text-xs italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Belum ada data peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODAL TOLAK PEMINJAMAN                                       --}}
    {{-- ============================================================ --}}
    <div id="modalTolak"
         class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
         onclick="handleOverlayClick(event)">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6"
             onclick="event.stopPropagation()">

            {{-- Header modal --}}
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="text-rose-400"><i class="fas fa-ban"></i></span>
                        Tolak Pengajuan Peminjaman
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">
                        Peminjam: <span id="modalNamaPeminjam" class="text-slate-300 font-medium"></span>
                    </p>
                </div>
                <button type="button" onclick="closeModalTolak()"
                        class="text-slate-500 hover:text-slate-300 transition text-lg leading-none mt-0.5">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <p class="text-sm text-slate-400 mb-4">
                Isi alasan penolakan di bawah ini. Alasan akan dicatat di log aktivitas.
            </p>

            {{-- Form tolak --}}
            <form id="formTolak" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="alasanTolak"
                           class="block text-sm font-medium text-slate-300 mb-2">
                        Alasan Penolakan
                        <span class="text-rose-400">*</span>
                    </label>
                    <textarea id="alasanTolak"
                              name="alasan_tolak"
                              rows="4"
                              minlength="5"
                              maxlength="500"
                              placeholder="Contoh: Stok alat sedang tidak tersedia untuk tanggal tersebut."
                              class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-sm
                                     text-white placeholder-slate-500 resize-none
                                     focus:outline-none focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500/50
                                     transition"></textarea>
                    {{-- Karakter counter --}}
                    <div class="flex justify-between items-center mt-1">
                        <span id="alasanError" class="text-rose-400 text-xs hidden">
                            Alasan penolakan wajib diisi (minimal 5 karakter).
                        </span>
                        <span class="text-slate-600 text-xs ml-auto">
                            <span id="charCount">0</span>/500
                        </span>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeModalTolak()"
                            class="bg-slate-700 hover:bg-slate-600 text-white
                                   px-4 py-2 rounded-lg text-sm font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitTolak"
                            class="bg-rose-500 hover:bg-rose-600 text-white
                                   px-4 py-2 rounded-lg text-sm font-semibold transition
                                   flex items-center gap-2">
                        <i class="fas fa-ban text-xs"></i>
                        Tolak Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- JAVASCRIPT                                                   --}}
    {{-- ============================================================ --}}
    <script>
        /**
         * Buka modal tolak, isi action form dengan ID peminjaman yang dipilih.
         * @param {number} id       - ID peminjaman
         * @param {string} nama     - Nama peminjam untuk ditampilkan di modal
         */
        function openModalTolak(id, nama) {
            const modal = document.getElementById('modalTolak');
            const form  = document.getElementById('formTolak');
            const textarea = document.getElementById('alasanTolak');

            // Set action form ke route tolak dengan ID yang benar
            form.action = "{{ url('petugas/peminjaman') }}/" + id + "/tolak";

            // Tampilkan nama peminjam di modal
            document.getElementById('modalNamaPeminjam').textContent = nama;

            // Reset state modal
            textarea.value = '';
            document.getElementById('charCount').textContent = '0';
            document.getElementById('alasanError').classList.add('hidden');
            textarea.classList.remove('border-rose-500/60');

            // Tampilkan modal
            modal.classList.remove('hidden');

            // Fokus ke textarea setelah animasi
            setTimeout(() => textarea.focus(), 100);
        }

        /**
         * Tutup modal tolak.
         */
        function closeModalTolak() {
            document.getElementById('modalTolak').classList.add('hidden');
        }

        /**
         * Tutup modal jika klik di luar area modal (overlay).
         */
        function handleOverlayClick(event) {
            // event.target adalah overlay itu sendiri (bukan konten modal)
            closeModalTolak();
        }

        // Karakter counter untuk textarea
        document.getElementById('alasanTolak').addEventListener('input', function () {
            const len = this.value.length;
            document.getElementById('charCount').textContent = len;

            // Sembunyikan error saat user mulai mengetik
            if (len >= 5) {
                document.getElementById('alasanError').classList.add('hidden');
                this.classList.remove('border-rose-500/60');
            }
        });

        // Client-side validation sebelum submit
        document.getElementById('formTolak').addEventListener('submit', function (e) {
            const textarea  = document.getElementById('alasanTolak');
            const errorSpan = document.getElementById('alasanError');

            if (textarea.value.trim().length < 5) {
                e.preventDefault();
                errorSpan.classList.remove('hidden');
                textarea.classList.add('border-rose-500/60');
                textarea.focus();
                return false;
            }
        });

        // Tutup modal dengan tombol Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModalTolak();
            }
        });

        // Jika ada error validasi dari server (redirect back), buka ulang modal
        @if($errors->has('alasan_tolak'))
            // Tidak bisa buka ulang modal secara otomatis karena ID tidak tersimpan di session.
            // Error ditampilkan sebagai flash alert di atas tabel (sudah dihandle).
        @endif
    </script>

@endsection
