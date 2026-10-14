<!-- 1. Location Filter Card with Dropdown -->
<div class="relative bg-white rounded-xl shadow-sm p-4 flex items-center gap-3">
    <div class="text-slate-600 shrink-0">
        <i data-lucide="map-pin" class="w-6 h-6 text-[#0079C6]"></i>
    </div>
    <div class="flex-1 flex items-center gap-2">
        <span class="font-bold text-black text-sm md:text-base whitespace-nowrap">Pilih lokasi:</span>
        <div id="locationDropdownBtn" 
             data-current-kode="{{ $selectedKode ?? 1 }}"
             class="flex-1 relative flex items-center justify-between border border-[#0079C6] rounded-lg px-3 py-2 cursor-pointer hover:border-[#005f9e] transition-all bg-white select-none">
            <div class="flex items-center gap-2 overflow-hidden">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                <span id="selectedLocationText" class="text-sm text-slate-700 truncate">{{ $selectedLocation['name'] ?? 'True Blue' }}</span>
            </div>
            <i data-lucide="chevron-down" id="locationChevron" class="w-4 h-4 text-slate-400 shrink-0 ml-1 transition-transform"></i>
        </div>
    </div>

    <!-- Location Dropdown Menu (Figma Exact Styling) -->
    <div id="locationDropdownMenu" 
         class="hidden absolute top-[calc(100%+8px)] left-4 right-4 md:left-[110px] md:w-[380px] bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden z-50">
        <!-- Search input within dropdown -->
        <div class="p-2 border-b border-slate-100">
            <div class="flex items-center px-2 py-1 bg-slate-50 rounded-lg border border-slate-200">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 mr-2"></i>
                <input type="text" 
                       id="locationSearchInput"
                       placeholder="Cari lokasi..." 
                       class="w-full text-xs text-slate-700 bg-transparent focus:outline-none">
            </div>
        </div>

        <!-- Location List Options -->
        <div class="max-h-64 overflow-y-auto divide-y divide-slate-100" id="locationList">
            @include('dashboard.partials.location-options')
        </div>
    </div>
</div>
