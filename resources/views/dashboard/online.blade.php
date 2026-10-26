<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Online Monitoring - System Monitoring Otto</title>

    {{-- Google Fonts --}}
    @include('partials.fonts')

    {{-- CDN Dependencies (Tailwind, Lucide, Vite) --}}
    @include('partials.cdn')

    {{-- Custom Styles --}}
    @include('partials.styles')
</head>
<body class="bg-[#0079C6] min-h-screen text-slate-800 p-3 lg:p-5 flex flex-col">

    <!-- Overall Dashboard Wrapper -->
    <div class="w-full flex-1 flex flex-col md:flex-row gap-4 items-stretch relative">

        {{-- Collapsible Sidebar --}}
        @include('dashboard.partials.sidebar')

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col gap-4 min-w-0">

            {{-- Top Header Card --}}
            @include('dashboard.partials.header', ['headerTitle' => 'Online Monitoring'])

            <!-- 3 Summary Indicator Cards -->
            <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Card 1: Total Lokasi Terpantau -->
                <div class="bg-white rounded-xl shadow-md p-5 flex items-center justify-between">
                    <div>
                        <span class="text-xs md:text-sm font-bold text-slate-500 uppercase tracking-wider block">Total Lokasi Jaringan</span>
                        <div class="text-2xl md:text-3xl font-extrabold text-slate-900 mt-1">
                            {{ $summary['total_locations'] ?? 4 }} Lokasi
                        </div>
                        <span class="text-xs text-slate-400 mt-1 block">Semua titik onsite aktif</span>
                    </div>
                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-blue-50 text-[#0079C6] flex items-center justify-center shrink-0">
                        <i data-lucide="network" class="w-7 h-7"></i>
                    </div>
                </div>

                <!-- Card 2: Lokasi Online Normal -->
                <div class="bg-white rounded-xl shadow-md p-5 flex items-center justify-between">
                    <div>
                        <span class="text-xs md:text-sm font-bold text-slate-500 uppercase tracking-wider block">Koneksi Normal (Online)</span>
                        <div class="text-2xl md:text-3xl font-extrabold text-[#00B050] mt-1">
                            {{ $summary['online'] ?? 2 }} Lokasi
                        </div>
                        <span class="text-xs text-emerald-600 mt-1 font-semibold flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-[#00B050] inline-block animate-pulse"></span>
                            Stabil tanpa gangguan
                        </span>
                    </div>
                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-emerald-50 text-[#00B050] flex items-center justify-center shrink-0">
                        <i data-lucide="check-circle-2" class="w-7 h-7"></i>
                    </div>
                </div>

                <!-- Card 3: Gangguan & Offline -->
                <div class="bg-white rounded-xl shadow-md p-5 flex items-center justify-between">
                    <div>
                        <span class="text-xs md:text-sm font-bold text-slate-500 uppercase tracking-wider block">Perlu Perhatian / Gangguan</span>
                        <div class="text-2xl md:text-3xl font-extrabold text-[#FF0000] mt-1">
                            {{ ($summary['gangguan'] ?? 0) + ($summary['offline'] ?? 0) }} Lokasi
                        </div>
                        <span class="text-xs text-rose-500 mt-1 font-semibold flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-[#FF0000] inline-block"></span>
                            1 Gangguan, 1 Offline
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
                        <h2 class="text-lg md:text-xl font-bold text-black">Status Jaringan Onsite Parking</h2>
                        <p class="text-xs md:text-sm text-slate-500">Pemantauan real-time konektivitas dan ketersediaan jaringan per lokasi</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-[#0079C6] text-xs font-semibold rounded-full border border-blue-200">
                            <span class="w-2 h-2 rounded-full bg-[#0079C6] animate-ping"></span>
                            Live Auto-Refresh
                        </span>
                    </div>
                </div>

                <!-- Scrollable Table Container -->
                <div class="overflow-y-auto overflow-x-auto figma-scrollbar max-h-[600px] pr-2">
                    <table class="w-full text-center border-collapse">
                        <!-- Sticky Table Head -->
                        <thead class="sticky top-0 bg-white z-10">
                            <tr class="border-b-2 border-slate-900 text-black font-bold text-base md:text-lg">
                                <th class="py-3 px-4 text-left">Lokasi Onsite</th>
                                <th class="py-3 px-4 text-center">Jaringan</th>
                                <th class="py-3 px-4 text-center">Waktu henti (downtime)</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Riwayat</th>
                                <th class="py-3 px-4 text-center">Update terakhir</th>
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody id="networkTableBody" class="divide-y divide-slate-100 text-slate-800 text-sm md:text-base">
                            @foreach ($networkLocations as $item)
                                @php
                                    $isOnline = ($item['status'] === 'Online');
                                    $isGangguan = ($item['status'] === 'Gangguan');
                                    $isOffline = ($item['status'] === 'Offline');
                                    $isUnconfigured = ($item['status'] === '-');

                                    if ($isOnline) {
                                        $statusBadge = 'text-[#00B050] bg-emerald-50 border-emerald-200';
                                        $dotColor = 'bg-[#00B050]';
                                    } elseif ($isGangguan) {
                                        $statusBadge = 'text-amber-600 bg-amber-50 border-amber-200';
                                        $dotColor = 'bg-amber-500';
                                    } elseif ($isOffline) {
                                        $statusBadge = 'text-[#FF0000] bg-rose-50 border-rose-200';
                                        $dotColor = 'bg-[#FF0000]';
                                    } else {
                                        $statusBadge = 'text-slate-500 bg-slate-50 border-slate-200';
                                        $dotColor = 'bg-slate-400';
                                    }

                                    $downtimeColor = 'text-slate-600';
                                    if ($item['downtime'] === '0m (0%)') {
                                        $downtimeColor = 'text-[#00B050]';
                                    } elseif ($item['downtime'] !== '-') {
                                        $downtimeColor = 'text-[#FF0000]';
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <!-- Lokasi Onsite -->
                                    <td class="py-4 px-4 text-left">
                                        <div class="font-bold text-slate-900">{{ $item['name'] }}</div>
                                        <div class="text-xs text-slate-500 font-normal line-clamp-1 max-w-[240px]">{{ $item['address'] }}</div>
                                    </td>

                                    <!-- Jaringan -->
                                    <td class="py-4 px-4 font-normal text-slate-700">
                                        <div class="font-medium">{{ $item['jaringan'] }}</div>
                                    </td>

                                    <!-- Waktu Henti (Downtime) -->
                                    <td class="py-4 px-4 font-bold {{ $downtimeColor }}">
                                        {{ $item['downtime'] }}
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $statusBadge }}">
                                            @if(!$isUnconfigured)
                                                <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                                            @endif
                                            {{ $item['status'] }}
                                        </span>
                                    </td>

                                    <!-- Riwayat (Button to open popup) -->
                                    <td class="py-4 px-4">
                                        @if(empty($item['history']))
                                            <span class="text-slate-400 text-sm font-medium">-</span>
                                        @else
                                            <button type="button" 
                                                    onclick='openNetworkHistoryModal(@json($item))'
                                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-[#0079C6] hover:bg-[#005f9e] active:scale-95 text-white text-xs md:text-sm font-semibold rounded-lg shadow-sm transition-all">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                                <span>Lihat Riwayat</span>
                                            </button>
                                        @endif
                                    </td>

                                    <!-- Update Terakhir -->
                                    <td class="py-4 px-4 font-normal text-slate-500 text-xs md:text-sm">
                                        {{ $item['update'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

    {{-- Network History Popup Modal --}}
    @include('dashboard.partials.network-history-modal')

    <!-- Interactive JavaScript -->
    <script>
        // Initialize Lucide Icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Sidebar shrink/expand logic
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarFullLogo = document.getElementById('sidebarFullLogo');
        const navLabels = document.querySelectorAll('.nav-label');

        let isExpanded = true;
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                isExpanded = !isExpanded;
                if (isExpanded) {
                    sidebar.classList.remove('md:w-20');
                    sidebar.classList.add('md:w-60');
                    sidebarFullLogo.classList.remove('hidden');
                    navLabels.forEach(lbl => lbl.classList.remove('hidden'));
                } else {
                    sidebar.classList.remove('md:w-60');
                    sidebar.classList.add('md:w-20');
                    sidebarFullLogo.classList.add('hidden');
                    navLabels.forEach(lbl => lbl.classList.add('hidden'));
                }
            });
        }

        // Network History Modal Handlers
        const networkHistoryModal = document.getElementById('networkHistoryModal');
        const networkHistoryBackdrop = document.getElementById('networkHistoryBackdrop');
        const historyModalLocationName = document.getElementById('historyModalLocationName');
        const historyModalDowntimeIndicator = document.getElementById('historyModalDowntimeIndicator');
        const historyModalUptimeIndicator = document.getElementById('historyModalUptimeIndicator');
        const historyModalTableBody = document.getElementById('historyModalTableBody');

        window.openNetworkHistoryModal = function(locationData) {
            if (!locationData) return;

            if (historyModalLocationName) {
                historyModalLocationName.textContent = `${locationData.name} (${locationData.jaringan})`;
            }

            // Indicator metrics calculation
            if (locationData.status === 'Online') {
                historyModalDowntimeIndicator.textContent = '5m';
                historyModalUptimeIndicator.textContent = '99.9%';
                historyModalUptimeIndicator.className = 'text-2xl font-bold text-[#00B050] mt-1 block';
            } else if (locationData.status === 'Gangguan') {
                historyModalDowntimeIndicator.textContent = '37m';
                historyModalUptimeIndicator.textContent = '97.4%';
                historyModalUptimeIndicator.className = 'text-2xl font-bold text-amber-500 mt-1 block';
            } else {
                historyModalDowntimeIndicator.textContent = '3h 15m';
                historyModalUptimeIndicator.textContent = '89.2%';
                historyModalUptimeIndicator.className = 'text-2xl font-bold text-rose-600 mt-1 block';
            }

            // Populate table rows
            if (historyModalTableBody && locationData.history) {
                historyModalTableBody.innerHTML = locationData.history.map(row => {
                    const isNormal = (row.status === 'Normal');
                    const statusClass = isNormal 
                        ? 'text-[#00B050] font-bold' 
                        : (row.status.includes('Gangguan') || row.status.includes('RTO') ? 'text-amber-500 font-bold' : 'text-[#FF0000] font-bold');
                    const dtColor = (row.downtime === '0m') ? 'text-slate-700' : 'text-[#FF0000] font-bold';

                    return `
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 font-normal">${row.date}</td>
                            <td class="py-3 px-4 ${dtColor}">${row.downtime}</td>
                            <td class="py-3 px-4 ${statusClass}">${row.status}</td>
                        </tr>
                    `;
                }).join('');
            }

            if (networkHistoryModal) {
                networkHistoryModal.classList.remove('hidden');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        };

        window.closeNetworkHistoryModal = function() {
            if (networkHistoryModal) {
                networkHistoryModal.classList.add('hidden');
            }
        };

        // Close when clicking the dark background overlay
        if (networkHistoryBackdrop) {
            networkHistoryBackdrop.addEventListener('click', () => {
                closeNetworkHistoryModal();
            });
        }
    </script>
</body>
</html>

