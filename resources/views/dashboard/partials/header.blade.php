<!-- Top Header Card -->
<header class="bg-white rounded-xl shadow-md p-4 md:px-6 md:py-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <!-- Title & Subtitle -->
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-black tracking-tight">
            Dashboard System Monitoring Otto
        </h1>
        <span class="text-slate-400 font-bold text-base md:text-lg">Main</span>
    </div>

    <!-- User Profile & Action -->
    <div class="flex items-center gap-3 self-end md:self-center">
        <div class="text-right">
            <div class="text-sm md:text-base font-bold text-black">{{ Auth::user()->name ?? 'Otto Parking' }}</div>
            <div class="text-xs md:text-sm font-bold text-[#0037FF]">{{ Auth::user()->role ?? 'Admin' }}</div>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs md:text-sm font-bold text-[#FF0000] hover:underline cursor-pointer bg-transparent border-none p-0 inline">
                    Logout
                </button>
            </form>
        </div>
        <div class="w-12 h-12 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600">
            <i data-lucide="circle-user-round" class="w-9 h-9 text-slate-600"></i>
        </div>
    </div>
</header>
