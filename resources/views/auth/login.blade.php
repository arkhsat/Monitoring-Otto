<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Otto System Monitoring</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CDN fallback for standalone preview -->
    <script src="https://cdn.tailwindcss.com"></script>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center bg-cover bg-center bg-no-repeat relative p-4"
      style="background-image: url('{{ asset('images/bg-login.png') }}');">
    
    <!-- Optional Overlay for contrast -->
    <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-[2px]"></div>

    <!-- Login Container Box -->
    <div class="relative z-10 w-full max-w-[650px] bg-white rounded-[20px] shadow-2xl px-8 py-10 md:px-12 md:py-14 flex flex-col items-center">
        
        <!-- Logo -->
        <div class="w-full flex justify-center mb-8">
            <img src="{{ asset('images/logo-utama.png') }}" 
                 alt="Otto System Monitoring Logo" 
                 class="h-28 md:h-36 w-auto object-contain">
        </div>

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.post') }}" class="w-full flex flex-col gap-6">
            @csrf

            <!-- Username Field -->
            <div class="flex flex-col gap-2">
                <label for="username" class="text-slate-400 font-black text-lg md:text-xl tracking-tight">
                    Nama Pengguna
                </label>
                <div class="relative">
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="{{ old('username') }}"
                           required 
                           autocomplete="username"
                           autofocus
                           placeholder="Masukkan nama pengguna" 
                           class="w-full h-14 md:h-16 px-6 text-slate-800 text-lg border border-[#8F9EB3] rounded-[20px] focus:outline-none focus:ring-2 focus:ring-[#066AB7] focus:border-transparent transition-all shadow-sm">
                </div>
                @error('username')
                    <span class="text-sm font-semibold text-rose-500 mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="flex flex-col gap-2">
                <label for="password" class="text-slate-400 font-black text-lg md:text-xl tracking-tight">
                    Password
                </label>
                <div class="relative">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           autocomplete="current-password"
                           placeholder="Masukkan password" 
                           class="w-full h-14 md:h-16 px-6 text-slate-800 text-lg border border-[#8F9EB3] rounded-[20px] focus:outline-none focus:ring-2 focus:ring-[#066AB7] focus:border-transparent transition-all shadow-sm">
                </div>
                @error('password')
                    <span class="text-sm font-semibold text-rose-500 mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="mt-4">
                <button type="submit" 
                        class="w-full h-16 md:h-20 bg-[#066AB7] hover:bg-[#055696] active:scale-[0.99] text-white font-black text-xl md:text-2xl rounded-[20px] border border-[#9C9C9C] shadow-lg transition-all flex items-center justify-center cursor-pointer tracking-wide uppercase">
                    MASUK SISTEM
                </button>
            </div>
        </form>
    </div>

</body>
</html>
