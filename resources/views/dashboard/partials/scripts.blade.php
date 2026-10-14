<!-- Interactive JavaScript for Dashboard -->
<script>
    // 1. Initialize Lucide Icons
    lucide.createIcons();

    // 2. Sidebar Shrink / Expand Logic
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarFullLogo = document.getElementById('sidebarFullLogo');
    const navLabelMain = document.getElementById('navLabelMain');

    let isExpanded = true;
    sidebarToggle.addEventListener('click', () => {
        isExpanded = !isExpanded;
        if (isExpanded) {
            sidebar.classList.remove('md:w-20');
            sidebar.classList.add('md:w-60');
            sidebarFullLogo.classList.remove('hidden');
            navLabelMain.classList.remove('hidden');
        } else {
            sidebar.classList.remove('md:w-60');
            sidebar.classList.add('md:w-20');
            sidebarFullLogo.classList.add('hidden');
            navLabelMain.classList.add('hidden');
        }
    });

    // 3. Location Dropdown Logic
    const locationDropdownBtn = document.getElementById('locationDropdownBtn');
    const locationDropdownMenu = document.getElementById('locationDropdownMenu');
    const locationChevron = document.getElementById('locationChevron');
    const selectedLocationText = document.getElementById('selectedLocationText');
    const locationItems = document.querySelectorAll('.location-item');
    const locationSearchInput = document.getElementById('locationSearchInput');

    locationDropdownBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        calendarDropdownMenu.classList.add('hidden');
        calendarChevron.classList.remove('rotate-180');
        
        const isOpen = !locationDropdownMenu.classList.contains('hidden');
        if (isOpen) {
            locationDropdownMenu.classList.add('hidden');
            locationChevron.classList.remove('rotate-180');
        } else {
            locationDropdownMenu.classList.remove('hidden');
            locationChevron.classList.add('rotate-180');
            locationSearchInput.focus();
        }
    });

    locationItems.forEach(item => {
        item.addEventListener('click', () => {
            locationItems.forEach(i => {
                i.classList.remove('bg-[#BBE4FF]/50', 'bg-[#BBE4FF]');
            });
            item.classList.add('bg-[#BBE4FF]/50');
            selectedLocationText.textContent = item.getAttribute('data-name');
            locationDropdownMenu.classList.add('hidden');
            locationChevron.classList.remove('rotate-180');
        });
    });

    // Location search filter
    locationSearchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        locationItems.forEach(item => {
            const name = item.getAttribute('data-name').toLowerCase();
            const addr = item.getAttribute('data-address').toLowerCase();
            if (name.includes(query) || addr.includes(query)) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    });

    // 4. Calendar Popover & Filters Logic
    const calendarDropdownBtn = document.getElementById('calendarDropdownBtn');
    const calendarDropdownMenu = document.getElementById('calendarDropdownMenu');
    const calendarChevron = document.getElementById('calendarChevron');
    const selectedDateText = document.getElementById('selectedDateText');
    const dateTabs = document.querySelectorAll('.date-tab');
    const calDays = document.querySelectorAll('.cal-day');

    calendarDropdownBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        locationDropdownMenu.classList.add('hidden');
        locationChevron.classList.remove('rotate-180');

        const isOpen = !calendarDropdownMenu.classList.contains('hidden');
        if (isOpen) {
            calendarDropdownMenu.classList.add('hidden');
            calendarChevron.classList.remove('rotate-180');
        } else {
            calendarDropdownMenu.classList.remove('hidden');
            calendarChevron.classList.add('rotate-180');
        }
    });

    // Date Tabs (Daily, Weekly, Monthly, Yearly, Custom)
    dateTabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.stopPropagation();
            dateTabs.forEach(t => {
                t.classList.remove('bg-slate-100', 'text-black', 'border', 'border-slate-200');
                t.classList.add('hover:bg-slate-100');
            });
            tab.classList.add('bg-slate-100', 'text-black', 'border', 'border-slate-200');
            tab.classList.remove('hover:bg-slate-100');

            const mode = tab.getAttribute('data-mode');
            if (mode === 'daily') selectedDateText.textContent = '01/10/2026';
            if (mode === 'weekly') selectedDateText.textContent = '01/10/2026 - 07/10/2026';
            if (mode === 'monthly') selectedDateText.textContent = 'Oktober 2026';
            if (mode === 'yearly') selectedDateText.textContent = 'Tahun 2026';
            if (mode === 'custom') selectedDateText.textContent = 'Pilih rentang tanggal';
        });
    });

    // Day Selection
    calDays.forEach(day => {
        day.addEventListener('click', (e) => {
            e.stopPropagation();
            calDays.forEach(d => {
                d.classList.remove('bg-[#BBE4FF]', 'font-black');
            });
            day.classList.add('bg-[#BBE4FF]', 'font-black');
            const val = day.textContent.trim().padStart(2, '0');
            selectedDateText.textContent = `${val}/10/2026`;
            calendarDropdownMenu.classList.add('hidden');
            calendarChevron.classList.remove('rotate-180');
        });
    });

    // Close dropdowns on outside click
    document.addEventListener('click', (e) => {
        if (!locationDropdownBtn.contains(e.target) && !locationDropdownMenu.contains(e.target)) {
            locationDropdownMenu.classList.add('hidden');
            locationChevron.classList.remove('rotate-180');
        }
        if (!calendarDropdownBtn.contains(e.target) && !calendarDropdownMenu.contains(e.target)) {
            calendarDropdownMenu.classList.add('hidden');
            calendarChevron.classList.remove('rotate-180');
        }
    });
</script>
