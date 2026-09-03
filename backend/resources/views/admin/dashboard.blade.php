@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Peminjaman')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')
    <!-- Alert Selamat Datang -->
    <div class="mb-6 bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 p-4 rounded-xl">
        Selamat datang, <strong class="font-semibold text-white">{{ auth()->user()->name }}</strong>! Anda login sebagai hak akses
        <span class="uppercase font-bold text-indigo-400">{{ auth()->user()->role }}</span>.
    </div>

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