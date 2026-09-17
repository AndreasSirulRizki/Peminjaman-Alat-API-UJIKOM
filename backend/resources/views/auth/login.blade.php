<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Peminjaman Alat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }

        /* Animasi glow pada logo */
        @keyframes glow-pulse {
            0%, 100% { filter: drop-shadow(0 0 15px rgba(99,102,241,0.7)) drop-shadow(0 0 25px rgba(59,130,246,0.4)); }
            50%       { filter: drop-shadow(0 0 25px rgba(99,102,241,1)) drop-shadow(0 0 45px rgba(59,130,246,0.6)); }
        }
        .logo-glow { animation: glow-pulse 3s ease-in-out infinite; }

        /* Tombol gradient */
        .btn-gradient {
            background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%);
            transition: all 0.2s ease;
        }
        .btn-gradient:hover {
            opacity: 0.88;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }
        .btn-gradient:active { transform: translateY(0); }

        /* Input focus */
        .input-dark:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-slate-950 via-blue-950 to-slate-950 px-4">

    <div class="w-full max-w-md">

        {{-- ===== LOGO ===== --}}
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/peminjaman-alat.png') }}"
                 alt="Logo Sistem Peminjaman Alat"
                 class="w-40 h-40 object-contain logo-glow"
                 onerror="this.style.display='none'">
        </div>

        {{-- ===== JUDUL ===== --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-white mb-1">Sistem Peminjaman Alat</h1>
            <p class="text-sm text-slate-400">Masuk untuk melanjutkan</p>
        </div>

        {{-- ===== CARD FORM ===== --}}
        <div class="bg-slate-900/60 backdrop-blur border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/50 p-8">

            {{-- Alert error session --}}
            @if(session('error'))
                <div class="mb-4 p-3 bg-rose-500/10 border border-rose-500/25 text-rose-400 rounded-lg text-sm flex items-start gap-2">
                    <i class="fas fa-circle-exclamation mt-0.5 flex-shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Alert validation errors --}}
            @if($errors->any())
                <div class="mb-4 p-3 bg-rose-500/10 border border-rose-500/25 text-rose-400 rounded-lg text-sm">
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

            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm pointer-events-none"></i>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="Masukkan email Anda"
                            class="input-dark w-full pl-10 pr-3 py-2.5 bg-slate-800/50 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 transition">
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-5" x-data="{ show: false }">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm pointer-events-none"></i>
                        <input
                            :type="show ? 'text' : 'password'"
                            name="password"
                            required
                            placeholder="Masukkan password Anda"
                            class="input-dark w-full pl-10 pr-10 py-2.5 bg-slate-800/50 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 transition">
                        <button
                            type="button"
                            @click="show = !show"
                            :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition">
                            <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Tombol Masuk --}}
                <button type="submit"
                    class="btn-gradient w-full text-white font-semibold py-2.5 rounded-lg shadow-lg shadow-indigo-500/30 text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-right-to-bracket"></i>
                    Masuk
                </button>

            </form>
        </div>

        {{-- ===== FOOTER ===== --}}
        <p class="text-center text-xs text-slate-500 mt-6">
            Sistem Peminjaman Alat &copy; {{ date('Y') }}
        </p>

    </div>

</body>
</html>