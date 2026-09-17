<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js — untuk dropdown profil sidebar -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Font Awesome 6 (FREE) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Flatpickr — visual calendar date picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">

    <style>
        body { font-family: 'Inter', sans-serif; }

        /* ── Scrollbar ───────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* ── Flatpickr — sesuaikan warna popup dengan tema slate ─── */
        .flatpickr-calendar {
            background: #1e293b !important;   /* slate-800 */
            border: 1px solid #334155 !important; /* slate-700 */
            border-radius: 10px !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5) !important;
            font-family: 'Inter', sans-serif !important;
        }
        .flatpickr-months,
        .flatpickr-weekdays {
            background: #0f172a !important;   /* slate-950 */
        }
        .flatpickr-month {
            color: #f1f5f9 !important;        /* slate-100 */
        }
        .flatpickr-current-month input.cur-year,
        .flatpickr-current-month .flatpickr-monthDropdown-months {
            color: #f1f5f9 !important;
            background: transparent !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months option {
            background: #1e293b !important;
        }
        span.flatpickr-weekday {
            color: #94a3b8 !important;        /* slate-400 */
            font-size: 11px !important;
        }
        .flatpickr-day {
            color: #cbd5e1 !important;        /* slate-300 */
            border-radius: 6px !important;
        }
        .flatpickr-day:hover,
        .flatpickr-day:focus {
            background: #334155 !important;   /* slate-700 */
            border-color: #334155 !important;
            color: #fff !important;
        }
        .flatpickr-day.selected,
        .flatpickr-day.selected:hover {
            background: #6366f1 !important;   /* indigo-500 */
            border-color: #6366f1 !important;
            color: #fff !important;
        }
        .flatpickr-day.today {
            border-color: #6366f1 !important;
            color: #a5b4fc !important;        /* indigo-300 */
        }
        .flatpickr-day.today:hover {
            background: #4f46e5 !important;
            color: #fff !important;
        }
        .flatpickr-day.flatpickr-disabled,
        .flatpickr-day.prevMonthDay,
        .flatpickr-day.nextMonthDay {
            color: #475569 !important;        /* slate-600 */
        }
        .flatpickr-prev-month svg,
        .flatpickr-next-month svg {
            fill: #94a3b8 !important;
        }
        .flatpickr-prev-month:hover svg,
        .flatpickr-next-month:hover svg {
            fill: #6366f1 !important;
        }
        /* Input field styling — pastikan cursor pointer */
        .datepicker-input {
            cursor: pointer !important;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- ======================== SIDEBAR ======================== -->
        <aside class="w-64 bg-slate-900 border-r border-slate-800 flex-col hidden md:flex">
            <!-- Logo -->
            <div class="p-5 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/peminjaman-alat.png') }}" alt="Logo Peminjaman Alat" class="w-8 h-8 rounded-lg object-cover">
                    <span class="text-lg font-bold tracking-wide text-white">Peminjaman Alat</span>
                </div>
            </div>

            <!-- Menu Navigasi -->
            <nav class="flex-1 p-4 space-y-1">

                <!-- ========================================================== -->
                <!-- DASHBOARD — Muncul untuk SEMUA ROLE (Admin & Petugas)      -->
                <!-- ========================================================== -->
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fas fa-tachometer-alt w-5 text-center"></i> Dashboard
                </a>

                <!-- ========================================================== -->
                <!-- MENU UNTUK ADMIN                                            -->
                <!-- ========================================================== -->
                @if(auth()->user()->role == 'admin')

                    <!-- Kelola User -->
                    <a href="{{ route('admin.user.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs('admin.user.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fas fa-users w-5 text-center"></i> Kelola User
                    </a>

                    <!-- Kelola Kategori -->
                    <a href="{{ route('admin.kategori.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs('admin.kategori.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fas fa-tags w-5 text-center"></i> Kelola Kategori
                    </a>

                    <!-- Kelola Alat -->
                    <a href="{{ route('admin.alat.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs('admin.alat.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fas fa-tools w-5 text-center"></i> Kelola Alat
                    </a>

                    <!-- Kelola Peminjaman -->
                    <a href="{{ route('admin.peminjaman.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs('admin.peminjaman.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fas fa-hand-holding w-5 text-center"></i> Kelola Peminjaman
                    </a>

                    <!-- Kelola Pengembalian -->
                    <a href="{{ route('admin.pengembalian.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs('admin.pengembalian.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fas fa-undo-alt w-5 text-center"></i> Kelola Pengembalian
                    </a>

                @elseif(auth()->user()->role == 'petugas')

                    <!-- ========================================================== -->
                    <!-- MENU KHUSUS PETUGAS (HANYA 2 MENU: Dashboard + Peminjaman)-->
                    <!-- ========================================================== -->

                    <!-- Kelola Peminjaman (Petugas) -->
                    <a href="{{ route('petugas.peminjaman.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition
                              {{ request()->routeIs('petugas.peminjaman.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fas fa-hand-holding w-5 text-center"></i> Kelola Peminjaman
                    </a>

                @endif

            </nav>

            <!-- Profil User — Dropdown -->
            <div class="p-4 border-t border-slate-800 relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="w-full flex items-center gap-3 rounded-lg px-2 py-1.5 hover:bg-slate-800 transition group">
                    {{-- Avatar: foto atau inisial --}}
                    @if(auth()->user()->foto_profile)
                        <img src="{{ Storage::url(auth()->user()->foto_profile) }}"
                             alt="Foto Profil"
                             class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                    @else
                        <div class="w-9 h-9 rounded-full bg-indigo-500/10 border border-indigo-500/20
                                    flex items-center justify-center text-sm font-bold text-indigo-400 flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="overflow-hidden flex-1 text-left">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                    <i class="fas fa-chevron-up text-slate-500 text-xs transition-transform group-hover:text-slate-300"
                       :class="open ? 'rotate-180' : ''"></i>
                </button>

                {{-- Dropdown menu --}}
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     @click.outside="open = false"
                     class="absolute bottom-full left-4 right-4 mb-1 bg-slate-800 border border-slate-700
                            rounded-xl shadow-xl overflow-hidden z-20">

                    {{-- Edit Profil --}}
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-2.5 px-4 py-3 text-sm text-slate-300
                              hover:bg-slate-700 hover:text-white transition">
                        <i class="fas fa-user-edit w-4 text-center text-indigo-400"></i>
                        Edit Profil
                    </a>

                    <div class="border-t border-slate-700"></div>

                    {{-- Logout --}}
                    <button type="button"
                        onclick="document.getElementById('modalLogout').classList.remove('hidden')"
                        class="w-full flex items-center gap-2.5 px-4 py-3 text-sm text-rose-400
                               hover:bg-rose-500/10 transition">
                        <i class="fas fa-sign-out-alt w-4 text-center"></i>
                        Logout
                    </button>
                </div>
            </div>
        </aside>

        <!-- ======================== MAIN CONTENT ======================== -->
        <div class="flex-1 flex flex-col overflow-y-auto bg-slate-950">

            <!-- Navbar Atas -->
            <header class="bg-slate-900/70 backdrop-blur border-b border-slate-800 h-16 flex items-center justify-between px-6 sticky top-0 z-10">
                <div class="text-lg font-semibold text-white">
                    @yield('header-title', 'Dashboard')
                </div>
                <div>
                    {{-- Jam Digital Real-time (WIB) --}}
                    <div id="jam-digital" class="text-sm text-slate-400 font-medium tabular-nums"></div>
                </div>
            </header>

            <!-- Konten Utama -->
            <main class="flex-1 p-6">
                @yield('content')
            </main>

        </div>
    </div>

    <!-- ======================== MODAL KONFIRMASI LOGOUT ======================== -->
    <div id="modalLogout" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
            <div class="flex flex-col items-center text-center gap-3">
                <div class="w-14 h-14 rounded-full bg-rose-500/10 border border-rose-500/20 flex items-center justify-center">
                    <i class="fas fa-sign-out-alt text-rose-400 text-xl"></i>
                </div>
                <h2 class="text-lg font-bold text-white">Konfirmasi Logout</h2>
                <p class="text-sm text-slate-400">Apakah kamu yakin ingin keluar dari sesi ini?</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="document.getElementById('modalLogout').classList.add('hidden')"
                    class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 font-semibold py-2.5 rounded-xl transition text-sm">
                    Tidak
                </button>
                <form action="{{ route('logout') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full bg-rose-500 hover:bg-rose-600 text-white font-semibold py-2.5 rounded-xl transition text-sm shadow-lg shadow-rose-500/20">
                        Ya, Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        /**
         * Inisialisasi Flatpickr secara global.
         * Semua input dengan class "datepicker-input" akan otomatis
         * menjadi kalender visual. Cukup tambahkan class tersebut
         * pada input di view mana saja — tidak perlu init ulang.
         */
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr('.datepicker-input', {
                locale      : 'id',          // Bahasa Indonesia (Januari, Februari, dst.)
                dateFormat  : 'Y-m-d',       // Format yang dikirim ke server (sesuai validasi controller)
                altInput    : true,           // Tampilkan format cantik ke user
                altFormat   : 'd F Y',        // Format tampilan: "01 Agustus 2024"
                allowInput  : false,          // User HARUS pilih dari kalender, tidak bisa ketik manual
                disableMobile: true,          // Paksa gunakan Flatpickr bahkan di HP (konsisten)
                prevArrow   : '‹',
                nextArrow   : '›',
                onReady: function(selectedDates, dateStr, instance) {
                    // Pastikan altInput ikut pakai class Tailwind yang sama
                    if (instance.altInput) {
                        instance.altInput.className = instance.input.className + ' datepicker-alt';
                        instance.altInput.removeAttribute('readonly');
                    }
                }
            });
        });
    </script>

    <!-- Jam Digital Real-time (WIB / Asia/Jakarta) -->
    <script>
        (function () {
            const HARI  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            const BULAN = ['Januari','Februari','Maret','April','Mei','Juni',
                           'Juli','Agustus','September','Oktober','November','Desember'];

            function updateJam() {
                const el = document.getElementById('jam-digital');
                if (!el) return;

                // Gunakan timezone Asia/Jakarta (WIB, UTC+7)
                const now  = new Date();
                const opts = { timeZone: 'Asia/Jakarta', hour12: false,
                               weekday:'long', day:'2-digit', month:'long', year:'numeric',
                               hour:'2-digit', minute:'2-digit', second:'2-digit' };

                // Ambil bagian tanggal & waktu secara terpisah supaya mudah diformat
                const fmt = new Intl.DateTimeFormat('id-ID', {
                    timeZone : 'Asia/Jakarta',
                    weekday  : 'long',
                    day      : '2-digit',
                    month    : 'long',
                    year     : 'numeric',
                    hour     : '2-digit',
                    minute   : '2-digit',
                    second   : '2-digit',
                    hour12   : false,
                }).formatToParts(now);

                const p = {};
                fmt.forEach(({ type, value }) => { p[type] = value; });

                // Format: "Rabu, 02 September 2026, 14:35:07"
                el.textContent = `${p.weekday}, ${p.day} ${p.month} ${p.year}, ${p.hour}:${p.minute}:${p.second}`;
            }

            document.addEventListener('DOMContentLoaded', function () {
                updateJam();
                setInterval(updateJam, 1000);
            });
        })();
    </script>

</body>
</html>