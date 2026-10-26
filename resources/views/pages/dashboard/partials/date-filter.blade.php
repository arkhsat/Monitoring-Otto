<!-- 2. Date Filter Card with Modal/Dropdown (Daily, Weekly, Monthly, Yearly, Custom) -->
<div class="relative bg-white rounded-xl shadow-sm p-4 flex items-center gap-3">
    <div class="text-slate-600 shrink-0">
        <i data-lucide="calendar-fold" class="w-6 h-6 text-[#0079C6]"></i>
    </div>
    <div class="flex-1 flex items-center gap-2">
        <span class="font-bold text-black text-sm md:text-base whitespace-nowrap">Atur tanggal:</span>
        <div id="calendarDropdownBtn" 
             class="flex-1 relative flex items-center justify-between border border-[#0079C6] rounded-lg px-3 py-2 cursor-pointer hover:border-[#005f9e] transition-all bg-white select-none">
            <span id="selectedDateText" class="text-sm font-medium text-slate-700">Semua Tanggal</span>
            <div class="flex items-center gap-1.5 text-slate-400">
                <i data-lucide="calendar" class="w-4 h-4 text-[#0079C6]"></i>
                <i data-lucide="chevron-down" id="calendarChevron" class="w-4 h-4 transition-transform"></i>
            </div>
        </div>
    </div>

    <!-- Figma Design Calendar Popover Modal -->
    <div id="calendarDropdownMenu" 
         class="hidden absolute top-[calc(100%+8px)] right-4 md:right-0 w-[360px] sm:w-[380px] bg-white rounded-2xl shadow-2xl border border-slate-200 p-4 z-50">
        
        <!-- Top Mode Navigation Pill (Figma Exact) -->
        <div class="bg-slate-100 p-1 rounded-full flex items-center justify-between text-xs font-semibold text-slate-600 mb-3">
            <button type="button" class="mode-tab flex-1 py-1.5 rounded-full text-center transition-all bg-white text-black font-bold shadow-sm" data-mode="daily">Daily</button>
            <button type="button" class="mode-tab flex-1 py-1.5 rounded-full text-center transition-all hover:text-black" data-mode="weekly">Weekly</button>
            <button type="button" class="mode-tab flex-1 py-1.5 rounded-full text-center transition-all hover:text-black" data-mode="monthly">Monthly</button>
            <button type="button" class="mode-tab flex-1 py-1.5 rounded-full text-center transition-all hover:text-black" data-mode="yearly">Yearly</button>
            <button type="button" class="mode-tab flex-1 py-1.5 rounded-full text-center transition-all hover:text-black" data-mode="custom">Custom</button>
        </div>

        <!-- Calendar Header (Month/Year display and Previous/Next buttons) -->
        <div class="flex items-center justify-between px-2 py-1 mb-2">
            <button id="calPrevBtn" class="p-1 hover:bg-slate-100 rounded-lg text-slate-700 transition-colors">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>
            <span id="calHeaderTitle" class="text-sm font-bold text-slate-900 select-none">October 2026</span>
            <button id="calNextBtn" class="p-1 hover:bg-slate-100 rounded-lg text-slate-700 transition-colors">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Container 1: Days View (for Daily, Weekly, Custom) -->
        <div id="daysViewContainer">
            <!-- Weekdays Header -->
            <div class="grid grid-cols-7 text-center text-xs text-slate-400 font-medium mb-1">
                <div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div><div>Su</div>
            </div>
            <!-- Days Grid Container -->
            <div id="calendarDaysGrid" class="grid grid-cols-7 text-center text-xs select-none">
                <!-- Dynamically generated cells -->
            </div>
        </div>

        <!-- Container 2: Monthly Grid View -->
        <div id="monthsViewContainer" class="hidden">
            <div id="monthsGrid" class="grid grid-cols-3 gap-3 p-2 text-center text-sm font-medium text-slate-700 select-none">
                <!-- Dynamically populated 12 months -->
            </div>
        </div>

        <!-- Container 3: Yearly Grid View -->
        <div id="yearsViewContainer" class="hidden">
            <div id="yearsGrid" class="grid grid-cols-3 gap-3 p-2 text-center text-sm font-medium text-slate-700 select-none">
                <!-- Dynamically populated decade years -->
            </div>
        </div>

        <!-- Custom Date Picker Helper Hint -->
        <div id="customHint" class="hidden mt-3 pt-2 border-t border-slate-100 text-[11px] text-center text-slate-500 font-medium">
            Klik tanggal awal, lalu klik tanggal akhir untuk rentang kustom.
        </div>
    </div>
</div>
