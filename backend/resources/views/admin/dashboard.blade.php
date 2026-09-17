@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Peminjaman')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')
    <!-- Alert Selamat Datang -->
    <div class="mb-6 bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 p-4 rounded-xl">
        Selamat datang, <strong class="font-semibold text-white">{{ auth()->user()->name }}</strong>! Anda login sebagai hak akses
        <span class="uppercase font-bold text-indigo-400">{{ auth()->user()->role }}</span>.
    </div>

    <!-- ======================================================== -->
    <!-- 4 CARD STATISTIK                                         -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <!-- Card 1: Total Alat -->
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-5 relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-400">Total Alat</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_alat'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-boxes text-indigo-400 text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3">Semua alat terdaftar</p>
        </div>

        <!-- Card 2: Sedang Dipinjam -->
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-5 relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-400">Sedang Dipinjam</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $stats['sedang_dipinjam'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-hand-holding text-blue-400 text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3">Peminjaman aktif saat ini</p>
        </div>

        <!-- Card 3: Pending Request -->
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-5 relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-400">Pending Request</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $stats['pending_request'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-clock text-amber-400 text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3">Menunggu persetujuan</p>
        </div>

        <!-- Card 4: Total User -->
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-5 relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-400">Total User</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_user'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-users text-emerald-400 text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3">Admin, petugas & peminjam</p>
        </div>

    </div>
    <!-- ======================================================== -->

    <!-- Tabel Log Aktivitas -->
    <div class="bg-slate-900 rounded-xl shadow-sm overflow-hidden border border-slate-800">
        <div class="p-5 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white">Log Aktivitas Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3 px-5 border-b border-slate-800">Waktu</th>
                        <th class="py-3 px-5 border-b border-slate-800">User</th>
                        <th class="py-3 px-5 border-b border-slate-800">Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-5 border-b border-slate-800 text-slate-500">{{ $log->created_at }}</td>
                            <td class="py-3 px-5 border-b border-slate-800 font-medium text-white">{{ $log->user->name ?? 'Sistem' }}</td>
                            <td class="py-3 px-5 border-b border-slate-800">{{ $log->aktivitas }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-slate-500">Belum ada aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection