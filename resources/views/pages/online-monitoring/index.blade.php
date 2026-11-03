@extends('layouts.app', [
    'title' => 'Online Monitoring - System Monitoring Otto',
    'headerTitle' => 'Online Monitoring'
])

@section('content')
    <!-- API Communication Error Banner (Auto-hidden, displayed only on API failure) -->
    <div id="apiErrorBanner" class="hidden bg-rose-50 border-l-4 border-[#FF0000] p-4 rounded-xl shadow-sm transition-all duration-300">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-rose-100 text-[#FF0000] flex items-center justify-center shrink-0">
                    <i data-lucide="wifi-off" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-rose-800">Koneksi ke API Monitoring Terputus</h4>
                    <p class="text-xs text-rose-600 mt-0.5">
                        Tidak dapat terhubung ke server backend API. Sedang mencoba menghubungkan kembali secara otomatis...
                    </p>
                </div>
            </div>
            <button type="button" 
                    onclick="fetchMonitoringData(true)" 
                    class="px-3 py-1.5 bg-[#FF0000] hover:bg-rose-700 active:scale-95 text-white text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                Coba Hubungkan Sekarang
            </button>
        </div>
    </div>

    <!-- 3 Summary Indicator Cards -->
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Total Lokasi Terpantau -->
        <div class="bg-white rounded-xl shadow-md p-5 flex items-center justify-between border border-slate-100">
            <div>
                <span class="text-xs md:text-sm font-bold text-slate-500 uppercase tracking-wider block">Total Lokasi Jaringan</span>
                <div id="summaryTotal" class="text-2xl md:text-3xl font-extrabold text-slate-900 mt-1">
                    {{ $summary['total_locations'] ?? 0 }} Lokasi
                </div>
                <span class="text-xs text-slate-400 mt-1 block">Semua titik onsite aktif</span>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-blue-50 text-[#0079C6] flex items-center justify-center shrink-0">
                <i data-lucide="network" class="w-7 h-7"></i>
            </div>
        </div>

        <!-- Card 2: Lokasi Online Normal -->
        <div class="bg-white rounded-xl shadow-md p-5 flex items-center justify-between border border-slate-100">
            <div>
                <span class="text-xs md:text-sm font-bold text-slate-500 uppercase tracking-wider block">Koneksi Normal (Online)</span>
                <div id="summaryOnline" class="text-2xl md:text-3xl font-extrabold text-[#00B050] mt-1">
                    {{ $summary['online'] ?? 0 }} Lokasi
                </div>
                <span class="text-xs text-emerald-600 mt-1 font-semibold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-[#00B050] inline-block animate-pulse"></span>
                    Update &lt; 5 menit lalu
                </span>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-emerald-50 text-[#00B050] flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-7 h-7"></i>
            </div>
        </div>

        <!-- Card 3: Gangguan & Offline -->
        <div class="bg-white rounded-xl shadow-md p-5 flex items-center justify-between border border-slate-100">
            <div>
                <span class="text-xs md:text-sm font-bold text-slate-500 uppercase tracking-wider block">Perlu Perhatian / Gangguan</span>
                <div id="summaryWarning" class="text-2xl md:text-3xl font-extrabold text-[#FF0000] mt-1">
                    {{ ($summary['gangguan'] ?? 0) + ($summary['offline'] ?? 0) }} Lokasi
                </div>
                <span id="summaryWarningDetail" class="text-xs text-rose-500 mt-1 font-semibold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-[#FF0000] inline-block"></span>
                    {{ $summary['gangguan'] ?? 0 }} Gangguan, {{ $summary['offline'] ?? 0 }} Offline
                </span>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-rose-50 text-[#FF0000] flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-7 h-7"></i>
            </div>
        </div>
    </section>

    <!-- Table Card for Network Monitoring on Each Location -->
    <section class="bg-white rounded-xl shadow-lg p-4 md:p-6 flex-1 flex flex-col min-h-[500px]">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-lg md:text-xl font-bold text-black flex items-center gap-2">
                    <span>Status Jaringan Onsite Parking</span>
                </h2>
                <p class="text-xs md:text-sm text-slate-500">
                    Real-time konektivitas jaringan per lokasi 
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Last Sync Status -->
                <span id="syncIndicator" class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-[#0079C6] text-xs font-semibold rounded-full border border-blue-200">
                    <span class="w-2 h-2 rounded-full bg-[#0079C6] animate-ping"></span>
                    <span id="syncText">Live Auto-Refresh (10s)</span>
                </span>
                <span id="lastSyncTime" class="text-[11px] text-slate-400 font-medium hidden sm:inline-block">
                    Sinkronisasi: Baru saja
                </span>
                <button type="button" 
                        onclick="fetchMonitoringData(true)" 
                        title="Segarkan data sekarang" 
                        class="p-1.5 text-slate-500 hover:text-[#0079C6] hover:bg-slate-100 rounded-lg transition-colors cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Scrollable Table Container -->
        <div class="overflow-y-auto overflow-x-auto figma-scrollbar max-h-[620px] pr-1">
            <table class="w-full text-center border-collapse">
                <!-- Sticky Table Head -->
                <thead class="sticky top-0 bg-white z-10">
                    <tr class="border-b-2 border-slate-900 text-black font-bold text-sm md:text-base">
                        <th class="py-3 px-4 text-left">Lokasi Onsite</th>
                        <!-- <th class="py-3 px-4 text-center">Jaringan</th> -->
                        <th class="py-3 px-4 text-center">Waktu Henti (Downtime)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Riwayat</th>
                        <th class="py-3 px-4 text-center">Update Terakhir</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody id="networkTableBody" class="divide-y divide-slate-100 text-slate-800 text-xs md:text-sm">
                    @forelse ($networkLocations as $item)
                        @php
                            $isOnline = ($item['status'] === 'Online');
                            $isGangguan = ($item['status'] === 'Gangguan');
                            $isOffline = ($item['status'] === 'Offline');

                            if ($isOnline) {
                                $statusBadge = 'text-[#00B050] bg-emerald-50 border-emerald-200';
                                $dotColor = 'bg-[#00B050]';
                            } elseif ($isGangguan) {
                                $statusBadge = 'text-amber-600 bg-amber-50 border-amber-200';
                                $dotColor = 'bg-amber-500';
                            } else {
                                $statusBadge = 'text-[#FF0000] bg-rose-50 border-rose-200';
                                $dotColor = 'bg-[#FF0000]';
                            }

                            $downtimeColor = 'text-slate-600';
                            if (str_starts_with($item['downtime_formatted'], '0m')) {
                                $downtimeColor = 'text-[#00B050] font-semibold';
                            } elseif ($item['downtime_formatted'] !== '-') {
                                $downtimeColor = 'text-[#FF0000] font-bold';
                            }
                        @endphp
                        <tr data-lokasi-id="{{ $item['lokasi_id'] }}" class="hover:bg-slate-50 transition-colors">
                            <!-- Lokasi Onsite -->
                            <td class="py-4 px-4 text-left">
                                <div class="font-bold text-slate-900 location-name">{{ $item['location'] }}</div>
                                <div class="text-[11px] text-slate-500 font-normal line-clamp-1 max-w-[240px] location-address">
                                    {{ $item['address'] }}
                                </div>
                            </td>

                            <!-- Jaringan -->
                            <!-- <td class="py-4 px-4 font-normal text-slate-700">
                                <div class="font-medium location-network">{{ $item['network'] }}</div>
                            </td> -->

                            <!-- Waktu Henti (Downtime 24h) -->
                            <td class="py-4 px-4 {{ $downtimeColor }} location-downtime">
                                <div>{{ $item['downtime_formatted'] }}</div>
                                <div class="text-[10px] text-slate-400 font-normal">Uptime: {{ $item['uptime_percentage'] }}%</div>
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4 location-status">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $statusBadge }}">
                                    <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                                    <span class="status-text">{{ $item['status'] }}</span>
                                </span>
                            </td>

                            <!-- Riwayat (Button to open popup) -->
                            <td class="py-4 px-4 location-history-btn">
                                <button type="button" 
                                        onclick="openNetworkHistoryModal({{ $item['lokasi_id'] }}, '{{ addslashes($item['location']) }}', '{{ addslashes($item['network']) }}')"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0079C6] hover:bg-[#005f9e] active:scale-95 text-white text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>Lihat Riwayat</span>
                                </button>
                            </td>

                            <!-- Update Terakhir (Dual Indicator: Absolute + Relative) -->
                            <td class="py-4 px-4 location-last-update">
                                <div class="font-semibold text-slate-800 text-xs update-absolute">
                                    {{ $item['last_update_absolute'] }}
                                </div>
                                <div class="text-[11px] text-slate-400 update-relative">
                                    {{ $item['last_update_relative'] }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyTableRow">
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                    <span class="text-sm font-medium">Belum ada data lokasi untuk dimonitor.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@section('modals')
    {{-- Network History Popup Modal --}}
    @include('pages.online-monitoring.partials.network-history-modal')
@endsection

@push('scripts')
<script>
    // =========================================================================
    // Real-Time Online Monitoring Engine (AJAX Polling & UI Delta Updater)
    // =========================================================================

    const POLL_INTERVAL = 10000; // 10 seconds
    let pollTimer = null;
    let abortController = null;
    let isPollingActive = true;
    let consecutiveErrors = 0;

    const apiErrorBanner = document.getElementById('apiErrorBanner');
    const syncText = document.getElementById('syncText');
    const lastSyncTime = document.getElementById('lastSyncTime');
    const summaryTotal = document.getElementById('summaryTotal');
    const summaryOnline = document.getElementById('summaryOnline');
    const summaryWarning = document.getElementById('summaryWarning');
    const summaryWarningDetail = document.getElementById('summaryWarningDetail');
    const networkTableBody = document.getElementById('networkTableBody');

    /**
     * Fetch updated monitoring dataset from RESTful API.
     */
    async function fetchMonitoringData(isManual = false) {
        // Cancel any pending request
        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        if (syncText) {
            syncText.textContent = isManual ? 'Menyinkronkan...' : 'Memperbarui...';
        }

        try {
            const response = await fetch('/api/online-monitoring', {
                signal: abortController.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`API responded with HTTP ${response.status}`);
            }

            const json = await response.json();
            if (!json.success || !json.data) {
                throw new Error(json.message || 'Format respons API tidak valid');
            }

            // Reset error counter and hide error banner
            consecutiveErrors = 0;
            if (apiErrorBanner) apiErrorBanner.classList.add('hidden');

            // 1. Update summary counters
            updateSummaryCards(json.summary);

            // 2. Perform delta DOM updates on table rows
            updateTableRows(json.data);

            // 3. Update sync indicator
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            if (lastSyncTime) {
                lastSyncTime.textContent = `Sinkronisasi: ${timeStr}`;
            }
            if (syncText) {
                syncText.textContent = 'Live Auto-Refresh (10s)';
            }

            // Refresh Lucide icons for any dynamically added DOM nodes
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

        } catch (err) {
            if (err.name === 'AbortError') return;

            console.error('Monitoring API poll failed:', err);
            consecutiveErrors++;

            if (syncText) {
                syncText.textContent = 'Gagal Sinkronisasi';
            }

            // Show distinct API connection error banner (distinguishing API failure from onsite network outages)
            if (apiErrorBanner) {
                apiErrorBanner.classList.remove('hidden');
            }
        } finally {
            scheduleNextPoll();
        }
    }

    /**
     * Schedule next poll with backoff on repeated errors.
     */
    function scheduleNextPoll() {
        if (!isPollingActive) return;

        if (pollTimer) clearTimeout(pollTimer);

        let delay = POLL_INTERVAL;
        if (consecutiveErrors > 0) {
            // Exponential backoff: 15s, 25s, max 45s
            delay = Math.min(45000, POLL_INTERVAL + (consecutiveErrors * 10000));
        }

        pollTimer = setTimeout(() => {
            fetchMonitoringData();
        }, delay);
    }

    /**
     * Update top summary indicator cards.
     */
    function updateSummaryCards(summary) {
        if (!summary) return;

        if (summaryTotal) summaryTotal.textContent = `${summary.total_locations || 0} Lokasi`;
        if (summaryOnline) summaryOnline.textContent = `${summary.online || 0} Lokasi`;

        const totalIssues = (summary.gangguan || 0) + (summary.offline || 0);
        if (summaryWarning) summaryWarning.textContent = `${totalIssues} Lokasi`;
        if (summaryWarningDetail) {
            summaryWarningDetail.innerHTML = `
                <span class="w-2 h-2 rounded-full bg-[#FF0000] inline-block"></span>
                ${summary.gangguan || 0} Gangguan, ${summary.offline || 0} Offline
            `;
        }
    }

    /**
     * Non-blocking delta DOM table updater.
     */
    function updateTableRows(items) {
        if (!networkTableBody || !items) return;

        // Remove empty placeholder row if data is present
        const emptyRow = document.getElementById('emptyTableRow');
        if (emptyRow && items.length > 0) {
            emptyRow.remove();
        }

        items.forEach(item => {
            let tr = networkTableBody.querySelector(`tr[data-lokasi-id="${item.lokasi_id}"]`);

            const isOnline = (item.status === 'Online');
            const isGangguan = (item.status === 'Gangguan');

            let statusBadgeClass = 'text-[#FF0000] bg-rose-50 border-rose-200';
            let dotColorClass = 'bg-[#FF0000]';

            if (isOnline) {
                statusBadgeClass = 'text-[#00B050] bg-emerald-50 border-emerald-200';
                dotColorClass = 'bg-[#00B050]';
            } else if (isGangguan) {
                statusBadgeClass = 'text-amber-600 bg-amber-50 border-amber-200';
                dotColorClass = 'bg-amber-500';
            }

            let downtimeColor = 'text-slate-600';
            if (item.downtime_formatted && item.downtime_formatted.startsWith('0m')) {
                downtimeColor = 'text-[#00B050] font-semibold';
            } else if (item.downtime_formatted !== '-') {
                downtimeColor = 'text-[#FF0000] font-bold';
            }

            if (tr) {
                // Delta update existing row to avoid flicker
                const netEl = tr.querySelector('.location-network');
                if (netEl) netEl.textContent = item.network || '-';

                const dtEl = tr.querySelector('.location-downtime');
                if (dtEl) {
                    dtEl.className = `py-4 px-4 ${downtimeColor} location-downtime`;
                    dtEl.innerHTML = `
                        <div>${item.downtime_formatted}</div>
                        <div class="text-[10px] text-slate-400 font-normal">Uptime: ${item.uptime_percentage}%</div>
                    `;
                }

                const statusEl = tr.querySelector('.location-status');
                if (statusEl) {
                    statusEl.innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border ${statusBadgeClass}">
                            <span class="w-2 h-2 rounded-full ${dotColorClass}"></span>
                            <span class="status-text">${item.status}</span>
                        </span>
                    `;
                }

                const updAbsEl = tr.querySelector('.update-absolute');
                if (updAbsEl) updAbsEl.textContent = item.last_update_absolute || '-';

                const updRelEl = tr.querySelector('.update-relative');
                if (updRelEl) updRelEl.textContent = item.last_update_relative || 'Belum pernah update';

            } else {
                // Append newly created location row
                const safeLocation = (item.location || '').replace(/'/g, "\\'");
                const safeNetwork = (item.network || '').replace(/'/g, "\\'");

                const newTr = document.createElement('tr');
                newTr.setAttribute('data-lokasi-id', item.lokasi_id);
                newTr.className = 'hover:bg-slate-50 transition-colors animate-in fade-in duration-200';
                newTr.innerHTML = `
                    <td class="py-4 px-4 text-left">
                        <div class="font-bold text-slate-900 location-name">${item.location}</div>
                        <div class="text-[11px] text-slate-500 font-normal line-clamp-1 max-w-[240px] location-address">
                            ${item.address || '-'}
                        </div>
                    </td>
                    <td class="py-4 px-4 font-normal text-slate-700">
                        <div class="font-medium location-network">${item.network}</div>
                    </td>
                    <td class="py-4 px-4 ${downtimeColor} location-downtime">
                        <div>${item.downtime_formatted}</div>
                        <div class="text-[10px] text-slate-400 font-normal">Uptime: ${item.uptime_percentage}%</div>
                    </td>
                    <td class="py-4 px-4 location-status">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border ${statusBadgeClass}">
                            <span class="w-2 h-2 rounded-full ${dotColorClass}"></span>
                            <span class="status-text">${item.status}</span>
                        </span>
                    </td>
                    <td class="py-4 px-4 location-history-btn">
                        <button type="button" 
                                onclick="openNetworkHistoryModal(${item.lokasi_id}, '${safeLocation}', '${safeNetwork}')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0079C6] hover:bg-[#005f9e] active:scale-95 text-white text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span>Lihat Riwayat</span>
                        </button>
                    </td>
                    <td class="py-4 px-4 location-last-update">
                        <div class="font-semibold text-slate-800 text-xs update-absolute">
                            ${item.last_update_absolute}
                        </div>
                        <div class="text-[11px] text-slate-400 update-relative">
                            ${item.last_update_relative}
                        </div>
                    </td>
                `;
                networkTableBody.appendChild(newTr);
            }
        });
    }

    // Tab visibility handling (pause polling on tab blur to save resources)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            isPollingActive = false;
            if (pollTimer) clearTimeout(pollTimer);
        } else {
            isPollingActive = true;
            fetchMonitoringData();
        }
    });

    // Start background polling loop
    scheduleNextPoll();


    // =========================================================================
    // Network History Popup Modal Logic
    // =========================================================================

    let currentHistoryLocationId = null;
    let currentHistoryLocationName = '';
    let currentHistoryNetwork = '';
    let activePreset = '30d';

    const networkHistoryModal = document.getElementById('networkHistoryModal');
    const networkHistoryBackdrop = document.getElementById('networkHistoryBackdrop');
    const historyModalLocationName = document.getElementById('historyModalLocationName');
    const historyModalDowntimeIndicator = document.getElementById('historyModalDowntimeIndicator');
    const historyModalUptimeIndicator = document.getElementById('historyModalUptimeIndicator');
    const historyModalIncidentIndicator = document.getElementById('historyModalIncidentIndicator');
    const historyModalPeriodLabel = document.getElementById('historyModalPeriodLabel');
    const historyRecordCount = document.getElementById('historyRecordCount');
    const historyModalTableBody = document.getElementById('historyModalTableBody');
    const historyStartDate = document.getElementById('historyStartDate');
    const historyEndDate = document.getElementById('historyEndDate');

    window.openNetworkHistoryModal = function(lokasiId, name, network) {
        currentHistoryLocationId = lokasiId;
        currentHistoryLocationName = name;
        currentHistoryNetwork = network;

        if (historyModalLocationName) {
            historyModalLocationName.textContent = `${name} (${network || '-'})`;
        }

        // Reset preset state
        activePreset = '30d';
        updatePresetButtonStyles();

        // Clear custom date inputs
        if (historyStartDate) historyStartDate.value = '';
        if (historyEndDate) historyEndDate.value = '';

        if (networkHistoryModal) {
            networkHistoryModal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // Fetch history data for this location
        loadHistoryData(lokasiId);
    };

    window.closeNetworkHistoryModal = function() {
        if (networkHistoryModal) {
            networkHistoryModal.classList.add('hidden');
        }
        currentHistoryLocationId = null;
    };

    if (networkHistoryBackdrop) {
        networkHistoryBackdrop.addEventListener('click', () => {
            closeNetworkHistoryModal();
        });
    }

    /**
     * Load history records from RESTful API.
     */
    async function loadHistoryData(lokasiId, params = {}) {
        if (!historyModalTableBody) return;

        // Show spinner loading state
        historyModalTableBody.innerHTML = `
            <tr>
                <td colspan="8" class="py-12 text-center text-slate-400">
                    <div class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-[#0079C6]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Memuat riwayat monitoring...</span>
                    </div>
                </td>
            </tr>
        `;

        let url = `/api/online-monitoring/${lokasiId}/history`;
        const queryParts = [];
        if (params.start_date) queryParts.push(`start_date=${encodeURIComponent(params.start_date)}`);
        if (params.end_date) queryParts.push(`end_date=${encodeURIComponent(params.end_date)}`);
        if (params.date) queryParts.push(`date=${encodeURIComponent(params.date)}`);

        if (queryParts.length > 0) {
            url += '?' + queryParts.join('&');
        }

        try {
            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }

            const json = await res.json();
            if (!json.success) {
                throw new Error(json.message || 'Gagal memuat riwayat');
            }

            renderHistoryContent(json);

        } catch (err) {
            console.error('Failed to load history:', err);
            historyModalTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="py-10 text-center text-rose-500">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-rose-400"></i>
                            <span class="font-semibold text-xs">Gagal mengambil data riwayat dari API.</span>
                            <button type="button" 
                                    onclick="loadHistoryData(${lokasiId})" 
                                    class="mt-1 px-3 py-1 bg-rose-100 hover:bg-rose-200 text-rose-700 text-xs font-semibold rounded-md">
                                Coba Lagi
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    /**
     * Render history metrics and table rows into modal.
     */
    function renderHistoryContent(data) {
        const metrics = data.metrics || {};
        const records = data.data || [];

        // 1. Indicators
        if (historyModalDowntimeIndicator) {
            historyModalDowntimeIndicator.textContent = metrics.total_downtime_formatted || '0m';
        }
        if (historyModalUptimeIndicator) {
            const uptime = metrics.uptime_percentage !== undefined ? metrics.uptime_percentage : 100;
            historyModalUptimeIndicator.textContent = `${uptime}%`;
            historyModalUptimeIndicator.className = uptime >= 99 
                ? 'text-2xl font-bold text-[#00B050] mt-1 block' 
                : (uptime >= 95 ? 'text-2xl font-bold text-amber-500 mt-1 block' : 'text-2xl font-bold text-rose-600 mt-1 block');
        }
        if (historyModalIncidentIndicator) {
            historyModalIncidentIndicator.textContent = `${metrics.total_incidents || 0} Insiden`;
        }
        if (historyRecordCount) {
            historyRecordCount.textContent = `${records.length} Catatan Ditemukan`;
        }

        // 2. Table Rows
        if (!records || records.length === 0) {
            historyModalTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="py-12 text-center text-slate-400 font-medium">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                            <span>Tidak ada catatan riwayat gangguan untuk periode ini.</span>
                        </div>
                    </td>
                </tr>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return;
        }

        historyModalTableBody.innerHTML = records.map(row => {
            const isOnline = (row.current_status === 'Online');
            const isGangguan = (row.current_status === 'Gangguan');

            let currentBadge = 'text-rose-700 bg-rose-50 border-rose-200';
            if (isOnline) currentBadge = 'text-[#00B050] bg-emerald-50 border-emerald-200';
            else if (isGangguan) currentBadge = 'text-amber-700 bg-amber-50 border-amber-200';

            const durClass = row.duration_seconds > 0 ? 'text-[#FF0000] font-bold' : 'text-slate-500';

            return `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 px-3 text-left font-medium text-slate-800">${row.recorded_at_formatted || '-'}</td>
                    <td class="py-3 px-3 text-center text-slate-600">${row.previous_status || '-'}</td>
                    <td class="py-3 px-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold border ${currentBadge}">
                            ${row.current_status}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-center text-slate-600">${row.downtime_start_formatted || '-'}</td>
                    <td class="py-3 px-3 text-center text-slate-600">${row.downtime_end_formatted || '-'}</td>
                    <td class="py-3 px-3 text-center ${durClass}">${row.duration_formatted || '0m'}</td>
                    <td class="py-3 px-3 text-center text-[#00B050] font-medium">${row.recovery_time_formatted || '-'}</td>
                    <td class="py-3 px-4 text-left text-slate-600 max-w-[220px] truncate" title="${(row.notes || '').replace(/"/g, '&quot;')}">
                        ${row.notes || '-'}
                    </td>
                </tr>
            `;
        }).join('');

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    /**
     * Apply preset date range filters.
     */
    window.applyHistoryPreset = function(preset) {
        if (!currentHistoryLocationId) return;
        activePreset = preset;
        updatePresetButtonStyles();

        const today = new Date();
        const formatDate = (d) => d.toISOString().split('T')[0];

        if (preset === '24h') {
            const yesterday = new Date(today.getTime() - (24 * 60 * 60 * 1000));
            loadHistoryData(currentHistoryLocationId, { start_date: formatDate(yesterday), end_date: formatDate(today) });
            if (historyModalPeriodLabel) historyModalPeriodLabel.textContent = '24 Jam Terakhir';
        } else if (preset === '7d') {
            const past7 = new Date(today.getTime() - (7 * 24 * 60 * 60 * 1000));
            loadHistoryData(currentHistoryLocationId, { start_date: formatDate(past7), end_date: formatDate(today) });
            if (historyModalPeriodLabel) historyModalPeriodLabel.textContent = '7 Hari Terakhir';
        } else if (preset === '30d') {
            const past30 = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
            loadHistoryData(currentHistoryLocationId, { start_date: formatDate(past30), end_date: formatDate(today) });
            if (historyModalPeriodLabel) historyModalPeriodLabel.textContent = '30 Hari Terakhir';
        } else {
            loadHistoryData(currentHistoryLocationId);
            if (historyModalPeriodLabel) historyModalPeriodLabel.textContent = 'Seluruh Catatan Riwayat';
        }
    };

    /**
     * Apply custom date inputs.
     */
    window.applyHistoryCustomDate = function() {
        if (!currentHistoryLocationId) return;

        const start = historyStartDate ? historyStartDate.value : null;
        const end = historyEndDate ? historyEndDate.value : null;

        if (!start && !end) {
            alert('Silakan pilih tanggal awal atau akhir.');
            return;
        }

        activePreset = 'custom';
        updatePresetButtonStyles();

        if (historyModalPeriodLabel) {
            historyModalPeriodLabel.textContent = `Rentang: ${start || 'Awal'} s/d ${end || 'Kini'}`;
        }

        loadHistoryData(currentHistoryLocationId, { start_date: start, end_date: end });
    };

    /**
     * Reset filter to 30d default.
     */
    window.resetHistoryFilter = function() {
        if (historyStartDate) historyStartDate.value = '';
        if (historyEndDate) historyEndDate.value = '';
        applyHistoryPreset('30d');
    };

    function updatePresetButtonStyles() {
        const presets = ['24h', '7d', '30d', 'all'];
        presets.forEach(p => {
            const btn = document.getElementById('preset' + p.charAt(0).toUpperCase() + p.slice(1));
            if (!btn) return;

            if (p === activePreset) {
                btn.className = 'px-2.5 py-1 rounded-md text-xs font-semibold bg-[#0079C6] text-white transition-colors';
            } else {
                btn.className = 'px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-[#0079C6] transition-colors';
            }
        });
    }
</script>
@endpush
