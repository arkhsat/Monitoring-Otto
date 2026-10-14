<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Otto System Monitoring</title>

    {{-- Fonts --}}
    @include('partials.fonts')

    {{-- CDN Dependencies (Tailwind, Lucide, Vite) --}}
    @include('partials.cdn')

    {{-- Custom Styles & Utilities --}}
    @include('partials.styles')
</head>
<body class="min-h-screen w-full flex items-center justify-center bg-login-otto relative p-4">

    <!-- Background Backdrop Overlay -->
    <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-[2px]"></div>

    <!-- Login Container Box -->
    <div class="relative z-10 w-full max-w-[650px] bg-white rounded-[20px] shadow-2xl px-8 py-10 md:px-12 md:py-14 flex flex-col items-center">
        
        <!-- Logo Header -->
        <div class="w-full flex justify-center mb-8">
            @include('partials.logo', [
                'alt' => 'Otto System Monitoring Logo',
                'class' => 'h-28 md:h-36 w-auto object-contain'
            ])
        </div>

        <!-- Login Form -->
        @include('auth.partials.form')

    </div>

    <!-- Interactive Scripts (Show/Hide Password, Form UX) -->
    @include('auth.partials.scripts')

</body>
</html>
