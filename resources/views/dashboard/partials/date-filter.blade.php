<!-- 2. Date Filter Card with Modal/Dropdown (Daily, Weekly, Monthly, Yearly, Custom) -->
<div class="relative bg-white rounded-xl shadow-sm p-4 flex items-center gap-3">
    <div class="text-slate-600 shrink-0">
        <i data-lucide="calendar-fold" class="w-6 h-6 text-[#0079C6]"></i>
    </div>
    <div class="flex-1 flex items-center gap-2">
        <span class="font-bold text-black text-sm md:text-base whitespace-nowrap">Atur tanggal:</span>
        <div id="calendarDropdownBtn" 
             class="flex-1 relative flex items-center justify-between border border-[#0079C6] rounded-lg px-3 py-2 cursor-pointer hover:border-[#005f9e] transition-all bg-white select-none">
            <span id="selectedDateText" class="text-sm font-medium text-slate-700">01/10/2026</span>
            <div class="flex items-center gap-1.5 text-slate-400">
                <i data-lucide="calendar" class="w-4 h-4 text-[#0079C6]"></i>
                <i data-lucide="chevron-down" id="calendarChevron" class="w-4 h-4 transition-transform"></i>
            </div>
        </div>
    </div>

    <!-- Figma Calendar Popover -->
    <div id="calendarDropdownMenu" 
         class="hidden absolute top-[calc(100%+8px)] right-4 md:right-0 w-[340px] bg-white rounded-2xl shadow-2xl border border-slate-200 p-4 z-50">
        
        <!-- Filter Tabs: Daily, Weekly, Monthly, Yearly, Custom -->
        <div class="flex items-center justify-between gap-1 pb-3 border-b border-slate-100 text-[11px] font-semibold text-slate-600">
            <button type="button" class="date-tab px-2 py-1 rounded-full bg-slate-100 text-black border border-slate-200" data-mode="daily">Daily</button>
            <button type="button" class="date-tab px-2 py-1 rounded-full hover:bg-slate-100 transition-colors" data-mode="weekly">Weekly</button>
            <button type="button" class="date-tab px-2 py-1 rounded-full hover:bg-slate-100 transition-colors" data-mode="monthly">Monthly</button>
            <button type="button" class="date-tab px-2 py-1 rounded-full hover:bg-slate-100 transition-colors" data-mode="yearly">Yearly</button>
            <button type="button" class="date-tab px-2 py-1 rounded-full hover:bg-slate-100 transition-colors" data-mode="custom">Custom</button>
        </div>

        <!-- Calendar Month Header -->
        <div class="flex items-center justify-between py-2 text-xs font-bold text-slate-800">
            <button id="prevMonthBtn" class="p-1 hover:bg-slate-100 rounded-md">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>
            <span id="calendarMonthTitle" class="text-sm font-bold">October 2026</span>
            <button id="nextMonthBtn" class="p-1 hover:bg-slate-100 rounded-md">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Calendar Days Grid -->
        <div class="grid grid-cols-7 gap-1 text-center text-xs mt-1">
            <!-- Weekdays -->
            <span class="text-slate-400 font-medium py-1">Mo</span>
            <span class="text-slate-400 font-medium py-1">Tu</span>
            <span class="text-slate-400 font-medium py-1">We</span>
            <span class="text-slate-400 font-medium py-1">Th</span>
            <span class="text-slate-400 font-medium py-1">Fr</span>
            <span class="text-slate-400 font-medium py-1">Sa</span>
            <span class="text-slate-400 font-medium py-1">Su</span>

            <!-- Days matching Figma mockup -->
            <span class="text-slate-300 py-1.5">28</span>
            <span class="text-slate-300 py-1.5">29</span>
            <span class="text-slate-300 py-1.5">30</span>
            <!-- Active Selected Day 1 -->
            <span class="cal-day font-black text-black bg-[#BBE4FF] rounded-lg py-1.5 cursor-pointer">1</span>
            <span class="cal-day font-bold text-slate-800 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">2</span>
            <span class="cal-day font-bold text-red-500 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">3</span>
            <span class="cal-day font-bold text-red-500 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">4</span>

            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">5</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">6</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">7</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">8</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">9</span>
            <span class="cal-day font-semibold text-red-500 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">10</span>
            <span class="cal-day font-semibold text-red-500 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">11</span>

            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">12</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">13</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">14</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">15</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">16</span>
            <span class="cal-day font-semibold text-red-500 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">17</span>
            <span class="cal-day font-semibold text-red-500 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">18</span>

            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">19</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">20</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">21</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">22</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">23</span>
            <span class="cal-day font-semibold text-red-500 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">24</span>
            <span class="cal-day font-semibold text-red-500 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">25</span>

            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">26</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">27</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">28</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">29</span>
            <span class="cal-day text-slate-700 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">30</span>
            <span class="cal-day font-semibold text-red-500 hover:bg-slate-100 rounded-lg py-1.5 cursor-pointer">31</span>
            <span class="text-slate-300 py-1.5">1</span>
        </div>
    </div>
</div>
