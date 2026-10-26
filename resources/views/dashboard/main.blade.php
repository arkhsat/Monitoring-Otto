<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard System Monitoring Otto</title>

    {{-- Google Fonts --}}
    @include('partials.fonts')

    {{-- CDN Dependencies (Tailwind, Lucide, Vite) --}}
    @include('partials.cdn')

    {{-- Custom Styles --}}
    @include('partials.styles')
</head>
<body class="bg-[#0079C6] min-h-screen text-slate-800 p-3 lg:p-5 flex flex-col">

    <!-- Overall Dashboard Wrapper -->
    <div class="w-full flex-1 flex flex-col md:flex-row gap-4 items-stretch relative">

        {{-- Collapsible Sidebar --}}
        @include('dashboard.partials.sidebar')

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col gap-4 min-w-0">

            {{-- Top Header Card --}}
            @include('dashboard.partials.header')

            {{-- Filter Controls Bar (Location & Date) --}}
            @include('dashboard.partials.filter-bar')

            {{-- Data Table Card --}}
            @include('dashboard.partials.table')

        </main>
    </div>

    {{-- Settlement & Confirmation Modals --}}
    @include('dashboard.partials.settlement-modal')

    {{-- Interactive JavaScript --}}
    @include('dashboard.partials.scripts')

</body>
</html>
