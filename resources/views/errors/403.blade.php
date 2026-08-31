<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Akses Ditolak | Portal Magang Pemkot Surabaya</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800 min-h-screen flex flex-col justify-between">

    <!-- Top Banner jika dalam sesi Impersonasi -->
    @if(session()->has('impersonator_id'))
        <div class="bg-gradient-to-r from-amber-600 via-rose-600 to-red-600 text-white px-4 py-2.5 shadow-lg flex items-center justify-between sticky top-0 z-[99999] text-xs sm:text-sm font-medium border-b border-rose-700">
            <div class="flex items-center gap-2">
                <span>Mode Penyamaran: Anda sedang mengelola akun <strong>{{ auth()->user()?->name ?? 'Pengguna' }}</strong> (Role: <span class="uppercase tracking-wider font-bold bg-white/20 px-1.5 py-0.5 rounded">{{ auth()->user()?->role ?? '-' }}</span>)</span>
            </div>
            <form action="{{ route('admin.impersonate.leave') }}" method="POST">
                @csrf
                <button type="submit" class="px-3 py-1 bg-white hover:bg-rose-50 text-rose-700 rounded-lg font-bold shadow-md transition text-xs flex items-center gap-1.5 cursor-pointer">
                    <span>Kembali ke Super Admin ({{ session('impersonator_name') }})</span>
                </button>
            </form>
        </div>
    @endif

    <div class="flex-1 flex items-center justify-center p-6">
        <div class="max-w-md w-full bg-white rounded-3xl p-8 border border-slate-100 shadow-xl text-center space-y-6">
            <div class="w-20 h-20 mx-auto bg-rose-50 rounded-3xl flex items-center justify-center border border-rose-100 text-3xl shadow-xs">
                🔒
            </div>

            <div class="space-y-2">
                <span class="inline-block px-3 py-1 text-xs font-black uppercase tracking-wider bg-rose-100 text-rose-700 rounded-full">
                    Akses Dibatasi (403)
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    Anda Tidak Memiliki Hak Akses
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                    {{ $exception->getMessage() ?: 'Halaman ini memerlukan hak akses khusus atau peran yang berbeda untuk membukanya.' }}
                </p>
            </div>

            @php
                $user = auth()->user();
                $dashboardUrl = route('dashboard');
                if ($user) {
                    if ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id))) {
                        $dashboardUrl = route('admin.dashboard');
                    } elseif ($user->role === 'admin') {
                        $dashboardUrl = route('admin.applications.index');
                    } elseif (in_array($user->role, ['mentor', 'pembimbing'])) {
                        $dashboardUrl = route('mentor.dashboard');
                    } elseif (in_array($user->role, ['dosen', 'academic_advisor'])) {
                        $dashboardUrl = route('lecturer.dashboard');
                    } elseif ($user->role === 'universitas') {
                        $dashboardUrl = route('university.dashboard');
                    }
                }
            @endphp

            <div class="pt-2 flex flex-col gap-2.5">
                @if(session()->has('impersonator_id'))
                    <form action="{{ route('admin.impersonate.leave') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/20 transition flex items-center justify-center gap-2 cursor-pointer">
                            <span>Kembali ke Super Admin ({{ session('impersonator_name') }})</span>
                        </button>
                    </form>
                @endif

                <a href="{{ $dashboardUrl }}" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2">
                    <span>Kembali ke Dashboard Utama</span>
                </a>

                <a href="{{ url('/') }}" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition">
                    Halaman Depan
                </a>
            </div>
        </div>
    </div>

    <footer class="p-4 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} Pemerintah Kota Surabaya. Sistem Informasi Penerimaan & Monitoring Magang.
    </footer>
</body>
</html>
