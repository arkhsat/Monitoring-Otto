<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Halaman Tidak Ditemukan | Otto System Monitoring</title>
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('images/logo-utama.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    {{-- Google Fonts --}}
    @include('components.fonts')

    {{-- CDN Dependencies (Tailwind, Lucide) --}}
    @include('components.cdn')
</head>
<body class="min-h-screen w-full flex items-center justify-center bg-login-otto bg-[#0079C6] relative p-4" style="background-image: url('{{ asset('images/bg-login.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <!-- Backdrop Overlay -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"></div>

    <!-- 404 Card -->
    <div class="relative z-10 w-full max-w-[540px] bg-white rounded-2xl shadow-2xl p-8 md:p-10 flex flex-col items-center text-center">
        <!-- Logo Header -->
        <div class="mb-6">
            @include('components.logo', [
                'alt' => 'Otto System Monitoring Logo',
                'class' => 'h-16 md:h-20 w-auto object-contain mx-auto'
            ])
        </div>

        <!-- 404 Badge -->
        <div class="w-16 h-16 rounded-full bg-blue-50 text-[#0079C6] flex items-center justify-center mb-4">
            <i data-lucide="file-question" class="w-8 h-8"></i>
        </div>

        <h1 class="text-3xl md:text-4xl font-black text-slate-800 tracking-tight mb-2">404</h1>
        <h2 class="text-lg md:text-xl font-bold text-slate-700 mb-2">Halaman Tidak Ditemukan</h2>
        <p class="text-sm text-slate-500 mb-6 max-w-sm">
            Maaf, tautan yang Anda tuju tidak tersedia, telah dihapus, atau sedang dalam pemeliharaan.
        </p>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <a href="{{ auth()->check() ? route('dashboard.main') : url('/') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#0079C6] hover:bg-[#005f9e] active:scale-95 text-white font-semibold text-sm rounded-xl shadow-md transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke {{ auth()->check() ? 'Dashboard' : 'Login' }}</span>
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
