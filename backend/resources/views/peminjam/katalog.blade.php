@extends('layouts.peminjam')

@section('title', 'Katalog Alat - Peminjam')
@section('header-title', 'Katalog Alat Tersedia')

@section('content')

    {{-- Flash: Sukses --}}
    @if(session('success'))
        <div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-sm flex items-start gap-2">
            <i class="fas fa-check-circle mt-0.5 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Flash: Error --}}
    @if(session('error'))
        <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-sm flex items-start gap-2">
            <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Validasi errors --}}
    @if($errors->any())
        <div class="mb-5 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-sm">
            <p class="font-semibold mb-1"><i class="fas fa-exclamation-triangle mr-2"></i>Terdapat kesalahan:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- HEADER + SEARCH BAR                                              --}}
    {{-- ================================================================ --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
            <h3 class="text-lg font-bold text-white">Katalog Alat</h3>
            <p class="text-sm text-slate-400 mt-0.5">
                Pilih alat, atur jumlah, lalu ajukan peminjaman.
            </p>
        </div>

        {{-- Keranjang badge --}}
        <div id="badge-keranjang"
             class="hidden items-center gap-2 bg-indigo-500/10 border border-indigo-500/20
                    text-indigo-300 text-sm font-semibold px-4 py-2 rounded-xl cursor-pointer
                    hover:bg-indigo-500/20 transition"
             onclick="scrollKeRingkasan()">
            <i class="fas fa-shopping-cart text-indigo-400"></i>
            <span id="badge-count">0</span> alat dipilih
            <i class="fas fa-chevron-down text-xs text-indigo-400 ml-1"></i>
        </div>
    </div>

    {{-- Search bar --}}
    <div class="relative mb-6">
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm pointer-events-none"></i>
        <input type="text"
               id="search-input"
               placeholder="Cari nama alat atau kategori..."
               autocomplete="off"
               class="w-full pl-10 pr-10 py-2.5 bg-slate-800 border border-slate-700 rounded-xl
                      text-slate-100 text-sm placeholder-slate-500
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                      transition">
        {{-- Clear button --}}
        <button type="button" id="btn-clear-search"
                onclick="clearSearch()"
                class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-500
                       hover:text-slate-300 transition">
            <i class="fas fa-times-circle"></i>
        </button>
    </div>

    {{-- ================================================================ --}}
    {{-- GRID KARTU ALAT                                                  --}}
    {{-- ================================================================ --}}

    {{-- Data alat encode ke JSON untuk dipakai JS --}}
    <div id="katalog-data"
         data-alats="{{ json_encode($alats->map(fn($a) => [
             'id'       => $a->id,
             'nama'     => $a->nama_alat,
             'kategori' => $a->kategori->nama_kategori ?? '-',
             'stok'     => $a->stok,
             'gambar'   => $a->gambar ? asset('foto/'.$a->gambar) : null,
         ])) }}"
         data-search-url="{{ route('peminjam.katalog.search') }}">
    </div>

    {{-- Grid render target --}}
    <div id="grid-alat"
         class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
        {{-- Di-render oleh JavaScript --}}
    </div>

    {{-- Empty state --}}
    <div id="empty-state" class="hidden py-20 text-center">
        <i class="fas fa-search text-4xl text-slate-600 mb-3 block"></i>
        <p class="text-slate-500">Tidak ada alat yang cocok dengan pencarian.</p>
        <button onclick="clearSearch()" class="mt-3 text-indigo-400 text-sm hover:underline">
            Tampilkan semua alat
        </button>
    </div>

    {{-- ================================================================ --}}
    {{-- PANEL RINGKASAN (sticky bawah → scroll ke anchor)               --}}
    {{-- ================================================================ --}}
    <div id="ringkasan-panel"
         class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl"
         style="scroll-margin-top: 1.5rem;">

        <div class="flex items-center justify-between mb-4">
            <h4 class="text-white font-bold text-base">
                <i class="fas fa-clipboard-list text-indigo-400 mr-2"></i>
                Ringkasan Peminjaman
            </h4>
            <span class="text-xs text-slate-500">
                <span id="ringkasan-count">0</span> alat dipilih
            </span>
        </div>

        {{-- Daftar alat yang dipilih (render JS) --}}
        <div id="ringkasan-list" class="mb-4 space-y-2">
            {{-- Placeholder kosong --}}
            <div id="ringkasan-empty" class="py-6 text-center text-slate-600 text-sm">
                <i class="fas fa-box-open text-2xl mb-2 block"></i>
                Belum ada alat dipilih. Klik "+ Pilih" pada kartu di atas.
            </div>
        </div>

        {{-- Form submit --}}
        <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST"
              id="form-peminjaman" onsubmit="return handleSubmit(event)">
            @csrf
            {{-- Hidden inputs alat_id[] dan jumlah[] → diisi JS sebelum submit --}}
            <div id="hidden-inputs"></div>

            <div class="border-t border-slate-800 pt-4 grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                {{-- Date picker --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        <i class="fas fa-calendar-alt text-indigo-400 mr-1"></i>
                        Rencana Tanggal Kembali
                    </label>
                    <input type="text"
                           id="tgl-kembali"
                           name="tgl_kembali_plan"
                           placeholder="Pilih tanggal kembali..."
                           required
                           autocomplete="off"
                           readonly
                           class="fp-katalog w-full px-3 py-2.5 bg-slate-800 border border-slate-700
                                  rounded-lg text-slate-100 text-sm cursor-pointer
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <p class="text-xs text-slate-600 mt-1">Tanggal harus lebih dari hari ini.</p>
                </div>

                {{-- Tombol Ajukan --}}
                <div>
                    <button type="submit"
                            class="w-full bg-indigo-500 hover:bg-indigo-600 active:bg-indigo-700
                                   text-white font-semibold py-2.5 px-5 rounded-xl transition
                                   shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        Ajukan Peminjaman
                    </button>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
{{-- Flatpickr — kalender date picker dengan tema dark --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    flatpickr('.fp-katalog', {
        locale       : 'id',
        dateFormat   : 'Y-m-d',       // format dikirim ke server
        altInput     : true,           // tampilkan format cantik ke user
        altFormat    : 'd F Y',        // contoh: "12 September 2026"
        allowInput   : false,
        disableMobile: true,
        minDate      : 'tomorrow',     // harus lebih dari hari ini
        prevArrow    : '‹',
        nextArrow    : '›',
        onReady: function (_, __, instance) {
            if (instance.altInput) {
                // Terapkan kelas Tailwind yang sama ke altInput
                instance.altInput.className =
                    'fp-katalog-alt w-full px-3 py-2.5 bg-slate-800 border border-slate-700 ' +
                    'rounded-lg text-slate-100 text-sm cursor-pointer ' +
                    'focus:outline-none focus:ring-2 focus:ring-indigo-500 transition ' +
                    'placeholder-slate-500';
                instance.altInput.placeholder = 'Pilih tanggal kembali...';
            }
        },
    });
});
</script>
<script>
(function () {
    'use strict';

    /* ─────────────────────────────────────────────
       STATE
    ───────────────────────────────────────────── */
    const dataEl    = document.getElementById('katalog-data');
    const ALL_ALATS = JSON.parse(dataEl.dataset.alats);   // semua alat dari server
    const SEARCH_URL = dataEl.dataset.searchUrl;

    let keranjang = {};   // { id: { nama, stok, jumlah, maxStok } }
    let searchTimer = null;
    let currentAlats = [...ALL_ALATS];

    /* ─────────────────────────────────────────────
       RENDER GRID KARTU
    ───────────────────────────────────────────── */
    function renderGrid(alats) {
        const grid  = document.getElementById('grid-alat');
        const empty = document.getElementById('empty-state');

        if (!alats.length) {
            grid.innerHTML  = '';
            empty.classList.remove('hidden');
            return;
        }
        empty.classList.add('hidden');

        grid.innerHTML = alats.map(alat => {
            const habis    = alat.stok <= 0;
            const dipilih  = keranjang[alat.id] !== undefined;
            const fotoHtml = alat.gambar
                ? `<img src="${alat.gambar}" alt="${escHtml(alat.nama)}"
                        class="w-full h-full object-cover transition duration-300 group-hover:scale-105"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                   <div class="w-full h-full items-center justify-center hidden">
                       <i class="fas fa-image text-slate-500 text-4xl"></i>
                   </div>`
                : `<div class="w-full h-full flex items-center justify-center">
                       <i class="fas fa-image text-slate-500 text-4xl"></i>
                   </div>`;

            const ringClass = dipilih
                ? 'border-indigo-500 shadow-lg shadow-indigo-500/10'
                : habis
                    ? 'border-slate-700/50 opacity-60'
                    : 'border-slate-700/50 hover:border-indigo-500/50 hover:shadow-lg hover:shadow-indigo-500/10';

            return `
            <div class="relative bg-slate-800/50 border ${ringClass} rounded-xl overflow-hidden transition duration-200 group"
                 id="card-${alat.id}">

                ${dipilih ? `<div class="absolute top-3 left-3 z-10 bg-indigo-500 text-white text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                    <i class="fas fa-check text-[10px]"></i> Dipilih
                </div>` : ''}

                ${habis ? `<div class="absolute top-3 right-3 z-10 bg-rose-500/80 backdrop-blur text-white text-xs font-semibold px-2 py-0.5 rounded-full">
                    Stok Habis
                </div>` : ''}

                {{-- Foto --}}
                <div class="h-40 overflow-hidden bg-slate-700/50">${fotoHtml}</div>

                {{-- Info --}}
                <div class="p-4">
                    <h4 class="text-white font-semibold text-sm leading-snug line-clamp-2 mb-1">
                        ${escHtml(alat.nama)}
                    </h4>
                    <p class="text-xs text-slate-400 mb-2">
                        <i class="fas fa-tag mr-1 text-slate-500"></i>${escHtml(alat.kategori)}
                    </p>
                    <p class="text-xs font-medium mb-3 ${habis ? 'text-rose-400' : 'text-emerald-400'}">
                        <i class="fas fa-circle text-[5px] mr-1 align-middle"></i>
                        ${habis ? 'Stok Habis' : 'Tersedia ' + alat.stok + ' unit'}
                    </p>

                    ${!habis ? `
                    ${dipilih ? `
                    <div class="flex items-center gap-2 mb-3">
                        <button type="button" onclick="stepKeranjang(${alat.id}, -1)"
                                class="w-7 h-7 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold flex items-center justify-center transition">−</button>
                        <input type="number" id="qty-${alat.id}"
                               value="${keranjang[alat.id].jumlah}" min="1" max="${alat.stok}"
                               onchange="setQty(${alat.id}, this.value)"
                               class="w-14 text-center px-2 py-1 bg-slate-700 border border-slate-600 rounded-lg
                                      text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button" onclick="stepKeranjang(${alat.id}, 1)"
                                class="w-7 h-7 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold flex items-center justify-center transition">+</button>
                        <span class="text-xs text-slate-500">/ ${alat.stok}</span>
                    </div>
                    <button type="button" onclick="hapusDariKeranjang(${alat.id})"
                            class="w-full py-2 rounded-lg text-sm font-semibold transition
                                   bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30
                                   flex items-center justify-center gap-1.5">
                        <i class="fas fa-trash-alt text-xs"></i> Batalkan Pilihan
                    </button>
                    ` : `
                    <button type="button" onclick="tambahKeKeranjang(${alat.id}, '${escJs(alat.nama)}', ${alat.stok})"
                            class="w-full py-2 rounded-lg text-sm font-semibold transition
                                   bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/30
                                   flex items-center justify-center gap-1.5">
                        <i class="fas fa-plus text-xs"></i> Pilih Alat
                    </button>
                    `}
                    ` : `
                    <button type="button" disabled
                            class="w-full py-2 rounded-lg text-sm font-semibold
                                   bg-slate-700/50 text-slate-500 cursor-not-allowed border border-slate-700
                                   flex items-center justify-center gap-1.5">
                        <i class="fas fa-ban text-xs"></i> Tidak Tersedia
                    </button>
                    `}
                </div>
            </div>`;
        }).join('');
    }

    /* ─────────────────────────────────────────────
       RENDER RINGKASAN PANEL
    ───────────────────────────────────────────── */
    function renderRingkasan() {
        const list    = document.getElementById('ringkasan-list');
        const empty   = document.getElementById('ringkasan-empty');
        const counter = document.getElementById('ringkasan-count');
        const badge   = document.getElementById('badge-keranjang');
        const badgeCnt= document.getElementById('badge-count');

        const items = Object.values(keranjang);
        counter.textContent  = items.length;
        badgeCnt.textContent = items.length;

        if (items.length > 0) {
            badge.classList.remove('hidden');
            badge.classList.add('flex');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('flex');
        }

        if (!items.length) {
            list.innerHTML = '';
            list.appendChild(empty);
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');
        list.innerHTML = items.map(item => `
            <div class="flex items-center justify-between bg-slate-800/60 border border-slate-700/50
                        rounded-xl px-4 py-3 gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">${escHtml(item.nama)}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Maks. stok: ${item.maxStok} unit</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button type="button" onclick="stepKeranjang(${item.id}, -1)"
                            class="w-7 h-7 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold flex items-center justify-center transition">−</button>
                    <span class="text-white font-bold text-sm w-6 text-center">${item.jumlah}</span>
                    <button type="button" onclick="stepKeranjang(${item.id}, 1)"
                            class="w-7 h-7 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold flex items-center justify-center transition">+</button>
                </div>
                <button type="button" onclick="hapusDariKeranjang(${item.id})"
                        class="text-slate-500 hover:text-rose-400 transition ml-1 flex-shrink-0">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        `).join('');
    }

    /* ─────────────────────────────────────────────
       KERANJANG ACTIONS
    ───────────────────────────────────────────── */
    window.tambahKeKeranjang = function (id, nama, maxStok) {
        if (keranjang[id]) return; // sudah ada
        keranjang[id] = { id, nama, jumlah: 1, maxStok };
        refresh();
    };

    window.hapusDariKeranjang = function (id) {
        delete keranjang[id];
        refresh();
    };

    window.stepKeranjang = function (id, step) {
        if (!keranjang[id]) return;
        const next = keranjang[id].jumlah + step;
        if (next < 1) { hapusDariKeranjang(id); return; }
        if (next > keranjang[id].maxStok) return;
        keranjang[id].jumlah = next;
        // sync input di card jika tampil
        const qtyInput = document.getElementById('qty-' + id);
        if (qtyInput) qtyInput.value = next;
        renderRingkasan();
    };

    window.setQty = function (id, val) {
        if (!keranjang[id]) return;
        const v = Math.max(1, Math.min(parseInt(val) || 1, keranjang[id].maxStok));
        keranjang[id].jumlah = v;
        const qtyInput = document.getElementById('qty-' + id);
        if (qtyInput) qtyInput.value = v;
        renderRingkasan();
    };

    function refresh() {
        renderGrid(currentAlats);
        renderRingkasan();
    }

    /* ─────────────────────────────────────────────
       SEARCH
    ───────────────────────────────────────────── */
    const searchInput = document.getElementById('search-input');
    const btnClear    = document.getElementById('btn-clear-search');

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();
        btnClear.classList.toggle('hidden', !q);

        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => doSearch(q), 300);
    });

    function doSearch(q) {
        if (!q) {
            currentAlats = [...ALL_ALATS];
            renderGrid(currentAlats);
            return;
        }

        // Filter lokal dulu (cepat, tanpa network)
        const lower = q.toLowerCase();
        currentAlats = ALL_ALATS.filter(a =>
            a.nama.toLowerCase().includes(lower) ||
            a.kategori.toLowerCase().includes(lower)
        );
        renderGrid(currentAlats);
    }

    window.clearSearch = function () {
        searchInput.value = '';
        btnClear.classList.add('hidden');
        currentAlats = [...ALL_ALATS];
        renderGrid(currentAlats);
        searchInput.focus();
    };

    /* ─────────────────────────────────────────────
       FORM SUBMIT — inject hidden inputs
    ───────────────────────────────────────────── */
    window.handleSubmit = function (e) {
        const items = Object.values(keranjang);
        if (!items.length) {
            alert('Pilih minimal 1 alat terlebih dahulu.');
            e.preventDefault();
            return false;
        }

        const container = document.getElementById('hidden-inputs');
        container.innerHTML = items.map(item => `
            <input type="hidden" name="alat_id[]" value="${item.id}">
            <input type="hidden" name="jumlah[${item.id}]" value="${item.jumlah}">
        `).join('');

        return true;
    };

    /* ─────────────────────────────────────────────
       SCROLL KE RINGKASAN
    ───────────────────────────────────────────── */
    window.scrollKeRingkasan = function () {
        document.getElementById('ringkasan-panel')
            .scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    /* ─────────────────────────────────────────────
       HELPERS
    ───────────────────────────────────────────── */
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
    function escJs(str) {
        return String(str).replace(/'/g, "\\'").replace(/\\/g, '\\\\');
    }

    /* ─────────────────────────────────────────────
       INIT
    ───────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        renderGrid(ALL_ALATS);
        renderRingkasan();
    });

})();
</script>
@endpush
