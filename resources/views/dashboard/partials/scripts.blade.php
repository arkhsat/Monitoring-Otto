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

        // -------------------------------------------------------------
        // 4. Enhanced Multi-Mode Calendar Engine (Figma Accurate)
        // -------------------------------------------------------------
    const calendarDropdownBtn = document.getElementById('calendarDropdownBtn');
    const calendarDropdownMenu = document.getElementById('calendarDropdownMenu');
    const calendarChevron = document.getElementById('calendarChevron');
    const selectedDateText = document.getElementById('selectedDateText');
        
        const modeTabs = document.querySelectorAll('.mode-tab');
        const calHeaderTitle = document.getElementById('calHeaderTitle');
        const calPrevBtn = document.getElementById('calPrevBtn');
        const calNextBtn = document.getElementById('calNextBtn');
        
        const daysViewContainer = document.getElementById('daysViewContainer');
        const monthsViewContainer = document.getElementById('monthsViewContainer');
        const yearsViewContainer = document.getElementById('yearsViewContainer');
        const customHint = document.getElementById('customHint');
        const calendarDaysGrid = document.getElementById('calendarDaysGrid');
        const monthsGrid = document.getElementById('monthsGrid');
        const yearsGrid = document.getElementById('yearsGrid');

        let currentMode = 'daily';
        let currentYear = 2026;
        let currentMonth = 9; // October (0-indexed: 9 = October)
        let decadeStartYear = 2020;

        // Selection states
        let selectedDay = new Date(2026, 9, 1);
        let selectedWeekStart = new Date(2026, 8, 28); // 28 Sept 2026 (Mo)
        let selectedWeekEnd = new Date(2026, 9, 4);   // 4 Oct 2026 (Su)
        let selectedMonthIndex = 1; // Feb (from Figma mockup)
        let selectedYear = 2021;    // 2021 (from Figma mockup)
        let customRangeStart = new Date(2026, 9, 1);
        let customRangeEnd = new Date(2026, 9, 23);
        let customHoverDate = null;
        let isSelectingCustom = false;

        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        const monthShortNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Helpers
        function formatDate(d) {
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        }

        function isSameDay(d1, d2) {
            return d1 && d2 &&
                   d1.getFullYear() === d2.getFullYear() &&
                   d1.getMonth() === d2.getMonth() &&
                   d1.getDate() === d2.getDate();
        }

        function isBetween(d, start, end) {
            if (!start || !end) return false;
            const t = d.getTime();
            const s = Math.min(start.getTime(), end.getTime());
            const e = Math.max(start.getTime(), end.getTime());
            return t >= s && t <= e;
        }

        // Toggle calendar dropdown open/close
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
                renderCurrentView();
        }
    });

        // Mode Tab Selection
        modeTabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.stopPropagation();
                modeTabs.forEach(t => {
                    t.classList.remove('bg-white', 'text-black', 'font-bold', 'shadow-sm');
                    t.classList.add('text-slate-600', 'hover:text-black');
            });
                tab.classList.add('bg-white', 'text-black', 'font-bold', 'shadow-sm');
                tab.classList.remove('text-slate-600', 'hover:text-black');

                currentMode = tab.getAttribute('data-mode');
                renderCurrentView();
        });
    });

        // Previous / Next Header Navigation
        calPrevBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (currentMode === 'monthly') {
                currentYear--;
            } else if (currentMode === 'yearly') {
                decadeStartYear -= 10;
            } else {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
            }
            renderCurrentView();
        });

        calNextBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (currentMode === 'monthly') {
                currentYear++;
            } else if (currentMode === 'yearly') {
                decadeStartYear += 10;
            } else {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
            }
            renderCurrentView();
        });

        function renderCurrentView() {
            daysViewContainer.classList.add('hidden');
            monthsViewContainer.classList.add('hidden');
            yearsViewContainer.classList.add('hidden');
            customHint.classList.add('hidden');

            if (currentMode === 'monthly') {
                monthsViewContainer.classList.remove('hidden');
                calHeaderTitle.textContent = `${currentYear}`;
                renderMonthsGrid();
            } else if (currentMode === 'yearly') {
                yearsViewContainer.classList.remove('hidden');
                calHeaderTitle.textContent = `${decadeStartYear} – ${decadeStartYear + 9}`;
                renderYearsGrid();
            } else {
                daysViewContainer.classList.remove('hidden');
                calHeaderTitle.textContent = `${monthNames[currentMonth]} ${currentYear}`;
                if (currentMode === 'custom') {
                    customHint.classList.remove('hidden');
                }
                renderDaysGrid();
            }
        }

        // Render Days Grid (Daily, Weekly, Custom)
        function renderDaysGrid() {
            calendarDaysGrid.innerHTML = '';

            const firstDay = new Date(currentYear, currentMonth, 1);
            // Monday is first day of week: (firstDay.getDay() + 6) % 7
            const startingDayOfWeek = (firstDay.getDay() + 6) % 7;
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const prevMonthDays = new Date(currentYear, currentMonth, 0).getDate();

            // Total 35 or 42 cells (5 or 6 weeks)
            const totalCells = (startingDayOfWeek + daysInMonth > 35) ? 42 : 35;

            for (let i = 0; i < totalCells; i++) {
                let cellDate;
                let isCurrentMonth = true;

                if (i < startingDayOfWeek) {
                    const prevD = prevMonthDays - startingDayOfWeek + i + 1;
                    cellDate = new Date(currentYear, currentMonth - 1, prevD);
                    isCurrentMonth = false;
                } else if (i >= startingDayOfWeek + daysInMonth) {
                    const nextD = i - (startingDayOfWeek + daysInMonth) + 1;
                    cellDate = new Date(currentYear, currentMonth + 1, nextD);
                    isCurrentMonth = false;
                } else {
                    const d = i - startingDayOfWeek + 1;
                    cellDate = new Date(currentYear, currentMonth, d);
                }

                const dayOfWeek = cellDate.getDay(); // 0 is Sunday, 6 is Saturday
                const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);

                const cellWrapper = document.createElement('div');
                cellWrapper.className = 'py-0.5 relative flex items-center justify-center cursor-pointer';

                const cellBtn = document.createElement('div');
                cellBtn.className = 'w-8 h-8 rounded-full flex items-center justify-center transition-all z-10 text-xs font-medium';
                cellBtn.textContent = cellDate.getDate();

                // Style based on Current Mode
                if (currentMode === 'daily') {
                    if (!isCurrentMonth) {
                        cellBtn.classList.add('text-slate-300');
                    } else if (isWeekend) {
                        cellBtn.classList.add('text-red-500');
                    } else {
                        cellBtn.classList.add('text-slate-800');
                    }

                    if (isSameDay(cellDate, selectedDay)) {
                        // Figma exact: rounded circular light-gray background with black font
                        cellBtn.className = 'w-8 h-8 rounded-full flex items-center justify-center z-10 text-xs font-bold bg-[#E5E7EB] text-black shadow-sm';
                    } else {
                        cellBtn.classList.add('hover:bg-slate-100');
                    }

                    cellWrapper.addEventListener('click', (e) => {
                        e.stopPropagation();
                        selectedDay = cellDate;
                        selectedDateText.textContent = formatDate(cellDate);
                        renderDaysGrid();
                    });

                } else if (currentMode === 'weekly') {
                    const inWeekRange = isBetween(cellDate, selectedWeekStart, selectedWeekEnd);
                    const isStart = isSameDay(cellDate, selectedWeekStart);
                    const isEnd = isSameDay(cellDate, selectedWeekEnd);

                    if (inWeekRange) {
                        // Figma: continuous light gray banner, with bright blue endpoints
                        const strip = document.createElement('div');
                        strip.className = 'absolute inset-y-0.5 inset-x-0 bg-[#F3F4F6] z-0';
                        if (isStart) strip.classList.add('rounded-l-full');
                        if (isEnd) strip.classList.add('rounded-r-full');
                        cellWrapper.appendChild(strip);

                        if (isStart || isEnd) {
                            cellBtn.className = 'w-8 h-8 rounded-full flex items-center justify-center z-10 text-xs font-bold bg-[#0079C6] text-white shadow-sm';
                        } else {
                            cellBtn.className = 'w-8 h-8 flex items-center justify-center z-10 text-xs text-slate-800 font-medium';
                        }
                    } else {
                        if (!isCurrentMonth) {
                            cellBtn.classList.add('text-slate-300');
                        } else if (isWeekend) {
                            cellBtn.classList.add('text-red-500');
                        } else {
                            cellBtn.classList.add('text-slate-800');
                        }
                        cellBtn.classList.add('hover:bg-slate-100');
                    }

                    // Selecting any day selects that whole 7-day span
                    cellWrapper.addEventListener('click', (e) => {
                        e.stopPropagation();
                        // Find Monday of the chosen week
                        const dayOffset = (cellDate.getDay() + 6) % 7;
                        selectedWeekStart = new Date(cellDate.getFullYear(), cellDate.getMonth(), cellDate.getDate() - dayOffset);
                        selectedWeekEnd = new Date(selectedWeekStart.getFullYear(), selectedWeekStart.getMonth(), selectedWeekStart.getDate() + 6);
                        selectedDateText.textContent = `${formatDate(selectedWeekStart)} - ${formatDate(selectedWeekEnd)}`;
                        renderDaysGrid();
                    });

                } else if (currentMode === 'custom') {
                    cellWrapper.setAttribute('data-timestamp', cellDate.getTime());
                    cellWrapper.setAttribute('data-weekend', isWeekend);
                    cellWrapper.setAttribute('data-current-month', isCurrentMonth);
                    cellBtn.setAttribute('data-day-number', cellDate.getDate());

                    // Click event: 1st click pins start; 2nd click pins end; subsequent click starts new range
                    cellWrapper.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (!isSelectingCustom) {
                            // 1st click: Pin start date
                            customRangeStart = cellDate;
                            customRangeEnd = null;
                            customHoverDate = cellDate;
                            isSelectingCustom = true;
                            selectedDateText.textContent = `${formatDate(customRangeStart)} - ...`;
                            updateCustomHighlightDOM();
                        } else {
                            // 2nd click: Pin end date and complete selection
                            isSelectingCustom = false;
                            if (cellDate.getTime() < customRangeStart.getTime()) {
                                customRangeEnd = customRangeStart;
                                customRangeStart = cellDate;
                            } else {
                                customRangeEnd = cellDate;
                            }
                            customHoverDate = null;
                            selectedDateText.textContent = `${formatDate(customRangeStart)} - ${formatDate(customRangeEnd)}`;
                            updateCustomHighlightDOM();
                        }
                    });

                    // Mouseenter event: only update styles of existing DOM elements without re-rendering the grid
                    cellWrapper.addEventListener('mouseenter', () => {
                        if (isSelectingCustom) {
                            customHoverDate = cellDate;
                            updateCustomHighlightDOM();
                        }
                    });
                }

                cellWrapper.appendChild(cellBtn);
                calendarDaysGrid.appendChild(cellWrapper);
            }

            if (currentMode === 'custom') {
                updateCustomHighlightDOM();
            }
        }

        // Live styling helper for Custom Mode (never replaces DOM or removes click listeners)
        function updateCustomHighlightDOM() {
            const effectiveEnd = isSelectingCustom ? (customHoverDate || customRangeStart) : customRangeEnd;
            const cells = calendarDaysGrid.querySelectorAll('[data-timestamp]');

            cells.forEach(wrapper => {
                const ts = parseInt(wrapper.getAttribute('data-timestamp'));
                const cellDate = new Date(ts);
                const isWeekend = wrapper.getAttribute('data-weekend') === 'true';
                const isCurrentMonth = wrapper.getAttribute('data-current-month') === 'true';
                const btn = wrapper.querySelector('div[data-day-number]');

                // Remove existing strip if any
                const existingStrip = wrapper.querySelector('.custom-range-strip');
                if (existingStrip) existingStrip.remove();

                const inRange = isBetween(cellDate, customRangeStart, effectiveEnd);
                const isStart = isSameDay(cellDate, customRangeStart);
                const isEnd = isSameDay(cellDate, effectiveEnd);

                if (inRange) {
                    const strip = document.createElement('div');
                    strip.className = 'custom-range-strip absolute inset-y-0.5 inset-x-0 bg-[#E0F2FE] z-0 pointer-events-none';
                    if (isStart) strip.classList.add('rounded-l-full');
                    if (isEnd) strip.classList.add('rounded-r-full');
                    wrapper.insertBefore(strip, btn);

                    if (isStart || isEnd) {
                        btn.className = 'w-8 h-8 rounded-full flex items-center justify-center z-10 text-xs font-bold bg-[#0079C6] text-white shadow-sm pointer-events-none';
                    } else {
                        btn.className = 'w-8 h-8 flex items-center justify-center z-10 text-xs font-medium pointer-events-none ' + (isWeekend ? 'text-red-500' : 'text-slate-800');
                    }
                } else {
                    btn.className = 'w-8 h-8 rounded-full flex items-center justify-center z-10 text-xs font-medium hover:bg-slate-100 pointer-events-none';
                    if (!isCurrentMonth) {
                        btn.classList.add('text-slate-300');
                    } else if (isWeekend) {
                        btn.classList.add('text-red-500');
                    } else {
                        btn.classList.add('text-slate-800');
                    }
                }
            });
        }

        // Render Months Grid (Monthly Mode)
        function renderMonthsGrid() {
            monthsGrid.innerHTML = '';
            monthShortNames.forEach((name, idx) => {
                const btn = document.createElement('div');
                btn.className = 'py-3 px-2 rounded-xl cursor-pointer transition-colors';
                btn.textContent = name;

                if (idx === selectedMonthIndex) {
                    // Figma exact: light grey rounded box
                    btn.classList.add('bg-[#F3F4F6]', 'text-black', 'font-bold');
                } else {
                    btn.classList.add('hover:bg-slate-100', 'text-slate-700');
                }

                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectedMonthIndex = idx;
                    currentMonth = idx;
                    selectedDateText.textContent = `${monthNames[idx]} ${currentYear}`;
                    renderMonthsGrid();
            });
                monthsGrid.appendChild(btn);
        });
        }

        // Render Years Grid (Yearly Mode)
        function renderYearsGrid() {
            yearsGrid.innerHTML = '';
            for (let y = decadeStartYear; y <= decadeStartYear + 9; y++) {
                const btn = document.createElement('div');
                btn.className = 'py-3 px-2 rounded-xl cursor-pointer transition-colors';
                btn.textContent = y;

                if (y === selectedYear) {
                    // Figma exact: light grey rounded box
                    btn.classList.add('bg-[#F3F4F6]', 'text-black', 'font-bold');
                } else {
                    btn.classList.add('hover:bg-slate-100', 'text-slate-700');
                }

                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectedYear = y;
                    currentYear = y;
                    selectedDateText.textContent = `Tahun ${y}`;
                    renderYearsGrid();
                });
                yearsGrid.appendChild(btn);
            }
        }

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

    // Initial render
    renderCurrentView();
</script>