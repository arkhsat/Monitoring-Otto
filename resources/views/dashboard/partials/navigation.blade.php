<!-- Navigation Links -->
<nav class="mt-6 flex flex-col gap-2">
    @php
        $currentRoute = Route::currentRouteName();
        $isMain = ($currentRoute === 'dashboard.main');
        $isOnline = ($currentRoute === 'dashboard.online');
        $isLokasi = (str_starts_with($currentRoute, 'dashboard.lokasi'));
    @endphp

    <a href="{{ route('dashboard.main') }}" 
       class="flex items-center gap-3 px-3 py-3 rounded-lg font-bold text-base md:text-lg transition-all {{ $isMain ? 'bg-[#0079C6] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
       title="Main Monitoring">
        <div class="w-6 h-6 flex items-center justify-center shrink-0">
            <i data-lucide="monitor" class="w-6 h-6"></i>
        </div>
        <span class="nav-label transition-opacity duration-200 whitespace-nowrap">Main</span>
    </a>

    <a href="{{ route('dashboard.online') }}" 
       class="flex items-center gap-3 px-3 py-3 rounded-lg font-bold text-base md:text-lg transition-all {{ $isOnline ? 'bg-[#0079C6] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
       title="Online Monitoring">
        <div class="w-6 h-6 flex items-center justify-center shrink-0">
            <i data-lucide="activity" class="w-6 h-6"></i>
        </div>
        <span class="nav-label transition-opacity duration-200 whitespace-nowrap">Online Monitoring</span>
    </a>

    <a href="{{ route('dashboard.lokasi') }}" 
       class="flex items-center gap-3 px-3 py-3 rounded-lg font-bold text-base md:text-lg transition-all {{ $isLokasi ? 'bg-[#0079C6] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
       title="Manajemen Lokasi">
        <div class="w-6 h-6 flex items-center justify-center shrink-0">
            <i data-lucide="map-pin" class="w-6 h-6"></i>
        </div>
        <span class="nav-label transition-opacity duration-200 whitespace-nowrap">Lokasi</span>
    </a>
</nav>
