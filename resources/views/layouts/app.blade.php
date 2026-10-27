<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard System Monitoring Otto' }}</title>
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('images/logo-utama.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    {{-- Google Fonts --}}
    @include('components.fonts')

    {{-- CDN Dependencies (Tailwind, Lucide, Vite) --}}
    @include('components.cdn')

    {{-- Page-Specific Styles --}}
    @stack('styles')
</head>
<body class="bg-[#0079C6] min-h-screen text-slate-800 p-3 lg:p-5 flex flex-col">

    <!-- Overall Dashboard Wrapper -->
    <div class="w-full flex-1 flex flex-col md:flex-row gap-4 items-stretch relative">

        {{-- Collapsible Sidebar --}}
        @include('components.sidebar')

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col gap-4 min-w-0">

            {{-- Top Header Card --}}
            @include('components.header', ['headerTitle' => $headerTitle ?? 'Main'])

            {{-- Primary Page Content Slot --}}
            @yield('content')

        </main>
    </div>

    {{-- Page-Specific Modals --}}
    @yield('modals')

    {{-- Global Script: Sidebar Toggle & Lucide initialization --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarFullLogo = document.getElementById('sidebarFullLogo');
            const navLabels = document.querySelectorAll('.nav-label');

            if (sidebar && sidebarToggle) {
                let isExpanded = true;
                sidebarToggle.addEventListener('click', () => {
                    isExpanded = !isExpanded;
                    if (isExpanded) {
                        sidebar.classList.remove('md:w-20');
                        sidebar.classList.add('md:w-60');
                        if (sidebarFullLogo) sidebarFullLogo.classList.remove('hidden');
                        navLabels.forEach(lbl => lbl.classList.remove('hidden'));
                    } else {
                        sidebar.classList.remove('md:w-60');
                        sidebar.classList.add('md:w-20');
                        if (sidebarFullLogo) sidebarFullLogo.classList.add('hidden');
                        navLabels.forEach(lbl => lbl.classList.add('hidden'));
                    }
                });
            }
        });
    </script>

    {{-- Page-Specific Scripts --}}
    @stack('scripts')

</body>
</html>
