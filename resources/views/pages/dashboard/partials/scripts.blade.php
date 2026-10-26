<script>
    // -------------------------------------------------------------
    // Location Dropdown & Dynamic Table Fetch
    // -------------------------------------------------------------
    const locationDropdownBtn = document.getElementById('locationDropdownBtn');
    const locationDropdownMenu = document.getElementById('locationDropdownMenu');
    const locationChevron = document.getElementById('locationChevron');
    const selectedLocationText = document.getElementById('selectedLocationText');
    const locationItems = document.querySelectorAll('.location-item');
    const locationSearchInput = document.getElementById('locationSearchInput');

    if (locationDropdownBtn && locationDropdownMenu) {
        locationDropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (calendarDropdownMenu) calendarDropdownMenu.classList.add('hidden');
            if (calendarChevron) calendarChevron.classList.remove('rotate-180');
            
            const isOpen = !locationDropdownMenu.classList.contains('hidden');
            if (isOpen) {
                locationDropdownMenu.classList.add('hidden');
                locationChevron.classList.remove('rotate-180');
            } else {
                locationDropdownMenu.classList.remove('hidden');
                locationChevron.classList.add('rotate-180');
                if (locationSearchInput) locationSearchInput.focus();
            }
        });
    }

    let activeLocationKode = locationDropdownBtn ? (locationDropdownBtn.getAttribute('data-current-kode') || 1) : 1;

    function fetchTableData(kode, date = null) {
        const tableBody = document.getElementById('tableBody');
        if (!tableBody) return;

        // Visual loading indicator
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="py-12 text-center text-slate-500 font-medium">
                    <div class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-[#0079C6]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Memuat data dari database...</span>
                    </div>
                </td>
            </tr>
        `;

        let url = `{{ route('dashboard.data') }}?kode=${encodeURIComponent(kode)}`;
        if (date) {
            url += `&date=${encodeURIComponent(date)}`;
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.records || data.records.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400 font-medium">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                <span>Tidak ada data transaksi untuk lokasi ini.</span>
                            </div>
                        </td>
                    </tr>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
                return;
            }

            tableBody.innerHTML = data.records.map(row => {
                const isNotMatch = (row.status === 'Tidak Cocok');
                const statusColor = isNotMatch ? 'text-[#FF0000]' : 'text-[#00B050]';
                const actionCell = isNotMatch 
                    ? `<button type="button" 
                                onclick='openSettlementModal(${JSON.stringify(row)})'
                                class="inline-flex items-center justify-center px-5 py-1.5 bg-[#0079C6] hover:bg-[#005f9e] active:scale-95 text-white text-sm font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                            Send
                       </button>`
                    : `<span class="text-slate-300">-</span>`;

                return `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-4 px-4 font-normal text-slate-800">${row.date}</td>
                        <td class="py-4 px-4 font-bold ${statusColor}">${row.admin}</td>
                        <td class="py-4 px-4 font-bold ${statusColor}">${row.mdd}</td>
                        <td class="py-4 px-4 font-bold ${statusColor}">${row.report}</td>
                        <td class="py-4 px-4 font-bold ${statusColor}">
                            ${row.status}
                        </td>
                        <td class="py-4 px-4">
                            ${actionCell}
                        </td>
                    </tr>
                `;
            }).join('');

            if (typeof lucide !== 'undefined') lucide.createIcons();
        })
        .catch(err => {
            console.error('Error fetching location data:', err);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-8 text-center text-rose-500 font-medium">
                        Gagal memuat data dari database. Silakan coba lagi.
                    </td>
                </tr>
            `;
        });
    }

    locationItems.forEach(item => {
        item.addEventListener('click', () => {
            locationItems.forEach(i => {
                i.classList.remove('bg-[#BBE4FF]/50', 'bg-[#BBE4FF]');
            });
            item.classList.add('bg-[#BBE4FF]/50');
            const locName = item.getAttribute('data-name');
            const locKode = item.getAttribute('data-kode') || item.getAttribute('data-id') || 1;
            
            if (selectedLocationText) selectedLocationText.textContent = locName;
            if (locationDropdownMenu) locationDropdownMenu.classList.add('hidden');
            if (locationChevron) locationChevron.classList.remove('rotate-180');

            activeLocationKode = locKode;
            if (locationDropdownBtn) {
                locationDropdownBtn.setAttribute('data-current-kode', locKode);
            }

            // Keep URL query param synced
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('kode', locKode);
            window.history.replaceState({}, '', currentUrl);

            // Fetch table data from database for selected location
            fetchTableData(locKode);
        });
    });

    // Location search filter
    if (locationSearchInput) {
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
    }

    // -------------------------------------------------------------
    // Enhanced Multi-Mode Calendar Engine (Figma Accurate)
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

    let selectedDay = new Date(2026, 9, 1);
    let selectedWeekStart = new Date(2026, 8, 28);
    let selectedWeekEnd = new Date(2026, 9, 4);
    let selectedMonthIndex = 9;
    let selectedYear = 2026;
    let customRangeStart = new Date(2026, 9, 1);
    let customRangeEnd = new Date(2026, 9, 23);
    let customHoverDate = null;
    let isSelectingCustom = false;

    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    const monthShortNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    function formatDate(d) {
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }

    function formatIsoDate(d) {
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${year}-${month}-${day}`;
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

    if (calendarDropdownBtn && calendarDropdownMenu) {
        calendarDropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (locationDropdownMenu) locationDropdownMenu.classList.add('hidden');
            if (locationChevron) locationChevron.classList.remove('rotate-180');

            const isOpen = !calendarDropdownMenu.classList.contains('hidden');
            if (isOpen) {
                calendarDropdownMenu.classList.add('hidden');
                if (calendarChevron) calendarChevron.classList.remove('rotate-180');
            } else {
                calendarDropdownMenu.classList.remove('hidden');
                if (calendarChevron) calendarChevron.classList.add('rotate-180');
                renderCurrentView();
            }
        });
    }

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

    if (calPrevBtn) {
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
    }

    if (calNextBtn) {
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
    }

    function renderCurrentView() {
        if (!daysViewContainer || !monthsViewContainer || !yearsViewContainer) return;
        daysViewContainer.classList.add('hidden');
        monthsViewContainer.classList.add('hidden');
        yearsViewContainer.classList.add('hidden');
        if (customHint) customHint.classList.add('hidden');

        if (currentMode === 'monthly') {
            monthsViewContainer.classList.remove('hidden');
            if (calHeaderTitle) calHeaderTitle.textContent = `${currentYear}`;
            renderMonthsGrid();
        } else if (currentMode === 'yearly') {
            yearsViewContainer.classList.remove('hidden');
            if (calHeaderTitle) calHeaderTitle.textContent = `${decadeStartYear} – ${decadeStartYear + 9}`;
            renderYearsGrid();
        } else {
            daysViewContainer.classList.remove('hidden');
            if (calHeaderTitle) calHeaderTitle.textContent = `${monthNames[currentMonth]} ${currentYear}`;
            if (currentMode === 'custom' && customHint) {
                customHint.classList.remove('hidden');
            }
            renderDaysGrid();
        }
    }

    function renderDaysGrid() {
        if (!calendarDaysGrid) return;
        calendarDaysGrid.innerHTML = '';

        const firstDay = new Date(currentYear, currentMonth, 1);
        const startingDayOfWeek = (firstDay.getDay() + 6) % 7;
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const prevMonthDays = new Date(currentYear, currentMonth, 0).getDate();

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

            const dayOfWeek = cellDate.getDay();
            const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);

            const cellWrapper = document.createElement('div');
            cellWrapper.className = 'py-0.5 relative flex items-center justify-center cursor-pointer';

            const cellBtn = document.createElement('div');
            cellBtn.className = 'w-8 h-8 rounded-full flex items-center justify-center transition-all z-10 text-xs font-medium';
            cellBtn.textContent = cellDate.getDate();

            if (currentMode === 'daily') {
                if (!isCurrentMonth) {
                    cellBtn.classList.add('text-slate-300');
                } else if (isWeekend) {
                    cellBtn.classList.add('text-red-500');
                } else {
                    cellBtn.classList.add('text-slate-800');
                }

                if (isSameDay(cellDate, selectedDay)) {
                    cellBtn.className = 'w-8 h-8 rounded-full flex items-center justify-center z-10 text-xs font-bold bg-[#E5E7EB] text-black shadow-sm';
                } else {
                    cellBtn.classList.add('hover:bg-slate-100');
                }

                cellWrapper.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectedDay = cellDate;
                    if (selectedDateText) selectedDateText.textContent = formatDate(cellDate);
                    renderDaysGrid();
                    fetchTableData(activeLocationKode, formatIsoDate(cellDate));
                });

            } else if (currentMode === 'weekly') {
                const inWeekRange = isBetween(cellDate, selectedWeekStart, selectedWeekEnd);
                const isStart = isSameDay(cellDate, selectedWeekStart);
                const isEnd = isSameDay(cellDate, selectedWeekEnd);

                if (inWeekRange) {
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

                cellWrapper.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const dayOffset = (cellDate.getDay() + 6) % 7;
                    selectedWeekStart = new Date(cellDate.getFullYear(), cellDate.getMonth(), cellDate.getDate() - dayOffset);
                    selectedWeekEnd = new Date(selectedWeekStart.getFullYear(), selectedWeekStart.getMonth(), selectedWeekStart.getDate() + 6);
                    if (selectedDateText) selectedDateText.textContent = `${formatDate(selectedWeekStart)} - ${formatDate(selectedWeekEnd)}`;
                    renderDaysGrid();
                });

            } else if (currentMode === 'custom') {
                cellWrapper.setAttribute('data-timestamp', cellDate.getTime());
                cellWrapper.setAttribute('data-weekend', isWeekend);
                cellWrapper.setAttribute('data-current-month', isCurrentMonth);
                cellBtn.setAttribute('data-day-number', cellDate.getDate());

                cellWrapper.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (!isSelectingCustom) {
                        customRangeStart = cellDate;
                        customRangeEnd = null;
                        customHoverDate = cellDate;
                        isSelectingCustom = true;
                        if (selectedDateText) selectedDateText.textContent = `${formatDate(customRangeStart)} - ...`;
                        updateCustomHighlightDOM();
                    } else {
                        isSelectingCustom = false;
                        if (cellDate.getTime() < customRangeStart.getTime()) {
                            customRangeEnd = customRangeStart;
                            customRangeStart = cellDate;
                        } else {
                            customRangeEnd = cellDate;
                        }
                        customHoverDate = null;
                        if (selectedDateText) selectedDateText.textContent = `${formatDate(customRangeStart)} - ${formatDate(customRangeEnd)}`;
                        updateCustomHighlightDOM();
                    }
                });

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

    function updateCustomHighlightDOM() {
        if (!calendarDaysGrid) return;
        const effectiveEnd = isSelectingCustom ? (customHoverDate || customRangeStart) : customRangeEnd;
        const cells = calendarDaysGrid.querySelectorAll('[data-timestamp]');

        cells.forEach(wrapper => {
            const ts = parseInt(wrapper.getAttribute('data-timestamp'));
            const cellDate = new Date(ts);
            const isWeekend = wrapper.getAttribute('data-weekend') === 'true';
            const isCurrentMonth = wrapper.getAttribute('data-current-month') === 'true';
            const btn = wrapper.querySelector('div[data-day-number]');

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

    function renderMonthsGrid() {
        if (!monthsGrid) return;
        monthsGrid.innerHTML = '';
        monthShortNames.forEach((name, idx) => {
            const btn = document.createElement('div');
            btn.className = 'py-3 px-2 rounded-xl cursor-pointer transition-colors';
            btn.textContent = name;

            if (idx === selectedMonthIndex) {
                btn.classList.add('bg-[#F3F4F6]', 'text-black', 'font-bold');
            } else {
                btn.classList.add('hover:bg-slate-100', 'text-slate-700');
            }

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                selectedMonthIndex = idx;
                currentMonth = idx;
                if (selectedDateText) selectedDateText.textContent = `${monthNames[idx]} ${currentYear}`;
                renderMonthsGrid();
            });
            monthsGrid.appendChild(btn);
        });
    }

    function renderYearsGrid() {
        if (!yearsGrid) return;
        yearsGrid.innerHTML = '';
        for (let y = decadeStartYear; y <= decadeStartYear + 9; y++) {
            const btn = document.createElement('div');
            btn.className = 'py-3 px-2 rounded-xl cursor-pointer transition-colors';
            btn.textContent = y;

            if (y === selectedYear) {
                btn.classList.add('bg-[#F3F4F6]', 'text-black', 'font-bold');
            } else {
                btn.classList.add('hover:bg-slate-100', 'text-slate-700');
            }

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                selectedYear = y;
                currentYear = y;
                if (selectedDateText) selectedDateText.textContent = `Tahun ${y}`;
                renderYearsGrid();
            });
            yearsGrid.appendChild(btn);
        }
    }

    // Close dropdowns on outside click
    document.addEventListener('click', (e) => {
        if (locationDropdownBtn && locationDropdownMenu && !locationDropdownBtn.contains(e.target) && !locationDropdownMenu.contains(e.target)) {
            locationDropdownMenu.classList.add('hidden');
            if (locationChevron) locationChevron.classList.remove('rotate-180');
        }
        if (calendarDropdownBtn && calendarDropdownMenu && !calendarDropdownBtn.contains(e.target) && !calendarDropdownMenu.contains(e.target)) {
            calendarDropdownMenu.classList.add('hidden');
            if (calendarChevron) calendarChevron.classList.remove('rotate-180');
        }
    });

    // -------------------------------------------------------------
    // Settlement & Confirmation Popup Modal Handlers
    // -------------------------------------------------------------
    const settlementModal = document.getElementById('settlementModal');
    const settlementBackdrop = document.getElementById('settlementBackdrop');
    const confirmModal = document.getElementById('confirmModal');
    const confirmBackdrop = document.getElementById('confirmBackdrop');

    const settlementModalDate = document.getElementById('settlementModalDate');
    const settlementModalAdmin = document.getElementById('settlementModalAdmin');
    const settlementModalMdd = document.getElementById('settlementModalMdd');
    const settlementModalReport = document.getElementById('settlementModalReport');

    window.openSettlementModal = function(data) {
        if (settlementModalDate && data.date) settlementModalDate.textContent = data.date;
        if (settlementModalAdmin && data.admin) settlementModalAdmin.textContent = 'Rp ' + data.admin;
        if (settlementModalMdd && data.mdd) settlementModalMdd.textContent = 'Rp ' + data.mdd;
        if (settlementModalReport && data.report) settlementModalReport.textContent = 'Rp ' + data.report;

        if (settlementModal) {
            settlementModal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    };

    window.closeSettlementModal = function() {
        if (settlementModal) {
            settlementModal.classList.add('hidden');
        }
    };

    window.openConfirmModal = function() {
        if (confirmModal) {
            confirmModal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    };

    window.closeConfirmModal = function() {
        if (confirmModal) {
            confirmModal.classList.add('hidden');
        }
    };

    window.handleKirimSettlement = function() {
        closeConfirmModal();
        closeSettlementModal();
    };

    if (settlementBackdrop) {
        settlementBackdrop.addEventListener('click', () => {
            closeSettlementModal();
        });
    }

    if (confirmBackdrop) {
        confirmBackdrop.addEventListener('click', () => {
            closeConfirmModal();
        });
    }
</script>
