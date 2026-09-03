<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Peminjaman Alat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-glow {
            background: radial-gradient(circle at top left, rgba(99,102,241,0.15), transparent 40%),
                        radial-gradient(circle at bottom right, rgba(99,102,241,0.1), transparent 40%);
        }
    </style>
</head>
<body class="bg-slate-950 bg-glow flex items-center justify-center min-h-screen text-slate-100">

    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <img src="{{ asset('images/peminjaman-alat.png') }}" alt="Logo Peminjaman Alat" class="w-24 h-24 rounded-xl object-contain mx-auto mb-4">
            <h1 class="text-2xl font-bold text-white">Sistem Peminjaman Alat</h1>
            <p class="text-slate-400 text-sm mt-1">Masuk untuk melanjutkan</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-8">

            @if(session('error'))
                <div class="mb-4 p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-lg text-sm">
                    <ul class="list-disc pl-5 mb-0 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>

                <button type="submit"
                    class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2.5 rounded-lg transition duration-200 shadow-lg shadow-indigo-500/20">
                    Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-slate-600 text-xs mt-6">Sistem Peminjaman Alat &copy; {{ date('Y') }}</p>
    </div>

</body>
</html>