@extends($layout)

@section('title', 'Edit Profil')

{{-- layouts.app (admin) pakai @section('header-title') --}}
@section('header-title', 'Edit Profil')
{{-- layouts.app-simple (petugas/peminjam) pakai @section('panel-title') --}}
@section('panel-title', 'Edit Profil')

@section('content')
<div class="max-w-2xl">

    {{-- Alert sukses --}}
    @if(session('success'))
        <div class="mb-5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-sm flex items-center gap-2">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Alert error validasi --}}
    @if($errors->any())
        <div class="mb-5 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-sm">
            <div class="flex items-center gap-2 mb-1 font-semibold"><i class="fas fa-exclamation-circle"></i> Terdapat kesalahan:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 p-6">

        {{-- Header kartu: avatar + info singkat --}}
        <div class="flex items-center gap-4 mb-6 pb-5 border-b border-slate-800">
            {{-- Avatar besar: foto atau inisial --}}
            <div class="relative">
                @if($user->foto_profile)
                    <img id="preview-foto"
                         src="{{ Storage::url($user->foto_profile) }}"
                         alt="Foto Profil"
                         class="w-20 h-20 rounded-full object-cover ring-2 ring-indigo-500/40">
                @else
                    <div id="preview-initials"
                         class="w-20 h-20 rounded-full bg-indigo-500/10 border border-indigo-500/20
                                flex items-center justify-center text-2xl font-bold text-indigo-400">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <img id="preview-foto"
                         src=""
                         alt="Foto Profil"
                         class="w-20 h-20 rounded-full object-cover ring-2 ring-indigo-500/40 hidden">
                @endif
            </div>
            <div>
                <p class="text-lg font-bold text-white">{{ $user->name }}</p>
                <p class="text-sm text-slate-500 capitalize">{{ $user->role }}</p>
                <p class="text-xs text-slate-600 mt-0.5">{{ $user->email }}</p>
            </div>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Nama Lengkap --}}
            <div class="mb-4">
                <label class="block text-slate-300 text-sm font-medium mb-2">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500">
            </div>

            {{-- Email (read-only) --}}
            <div class="mb-4">
                <label class="block text-slate-300 text-sm font-medium mb-2">
                    Email
                    <span class="text-xs text-slate-500 font-normal">(tidak dapat diubah)</span>
                </label>
                <input type="email" value="{{ $user->email }}" disabled
                    class="w-full px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg
                           text-slate-500 cursor-not-allowed">
            </div>

            {{-- No. HP --}}
            <div class="mb-4">
                <label class="block text-slate-300 text-sm font-medium mb-2">No. HP</label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                    placeholder="cth: 081234567890"
                    class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500">
            </div>

            {{-- Alamat --}}
            <div class="mb-4">
                <label class="block text-slate-300 text-sm font-medium mb-2">Alamat</label>
                <textarea name="alamat" rows="3"
                    placeholder="Masukkan alamat lengkap..."
                    class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500 resize-none">{{ old('alamat', $user->alamat) }}</textarea>
            </div>

            {{-- Foto Profil --}}
            <div class="mb-4">
                <label class="block text-slate-300 text-sm font-medium mb-2">
                    Foto Profil
                    <span class="text-xs text-slate-500 font-normal">(maks. 2 MB — JPG, PNG, GIF)</span>
                </label>
                <label for="foto_profile"
                    class="flex items-center gap-3 px-4 py-3 bg-slate-800 border border-slate-700 border-dashed
                           rounded-lg cursor-pointer hover:border-indigo-500/50 hover:bg-slate-800/80 transition group">
                    <i class="fas fa-cloud-upload-alt text-slate-500 group-hover:text-indigo-400 transition text-lg"></i>
                    <span id="foto-label" class="text-slate-500 group-hover:text-slate-300 text-sm transition">
                        {{ $user->foto_profile ? 'Klik untuk ganti foto' : 'Pilih foto dari perangkat' }}
                    </span>
                </label>
                <input type="file" id="foto_profile" name="foto_profile" accept="image/*"
                    class="hidden" onchange="previewFoto(this)">
            </div>

            {{-- Password Baru --}}
            <div class="mb-4">
                <label class="block text-slate-300 text-sm font-medium mb-2">
                    Password Baru
                    <span class="text-xs text-slate-500 font-normal">(kosongkan jika tidak ingin mengubah password)</span>
                </label>
                <input type="password" name="password"
                    placeholder="Minimal 6 karakter"
                    class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500">
            </div>

            {{-- Konfirmasi Password --}}
            <div class="mb-6">
                <label class="block text-slate-300 text-sm font-medium mb-2">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation"
                    placeholder="Ulangi password baru"
                    class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500">
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex justify-end gap-3">
                <button type="button" onclick="history.back()"
                    class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700
                           px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Batal
                </button>
                <button type="submit"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white px-5 py-2 rounded-lg
                           text-sm font-semibold transition shadow-lg shadow-indigo-500/20">
                    <i class="fas fa-save mr-1.5"></i> Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    /**
     * Preview foto profil saat user memilih file,
     * sebelum form di-submit.
     */
    function previewFoto(input) {
        const label = document.getElementById('foto-label');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            label.textContent = file.name;

            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.getElementById('preview-foto');
                const initials = document.getElementById('preview-initials');

                img.src = e.target.result;
                img.classList.remove('hidden');

                if (initials) initials.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endsection
