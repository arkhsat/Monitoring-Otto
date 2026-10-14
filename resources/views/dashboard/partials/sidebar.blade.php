<!-- Collapsible / Shrinkable Sidebar -->
<aside id="sidebar" 
       class="bg-white rounded-xl shadow-lg transition-all duration-300 ease-in-out flex flex-col p-3 shrink-0 w-full md:w-60"
       data-expanded="true">
    
    <!-- Sidebar Header: Menu Toggle Icon & Logo -->
    <div class="flex items-center gap-2 pb-4 border-b border-slate-100 justify-between">
        <button id="sidebarToggle" 
                title="Toggle Sidebar" 
                class="p-2 text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer flex items-center justify-center">
            <i data-lucide="menu" class="w-7 h-7"></i>
        </button>

        <!-- Full Logo (hidden when shrunk) -->
        <div id="sidebarFullLogo" class="flex-1 flex justify-center transition-opacity duration-200">
            @include('partials.logo', ['class' => 'h-10 w-auto object-contain'])
        </div>
    </div>

    <!-- Navigation Links -->
    @include('dashboard.partials.navigation')
</aside>
