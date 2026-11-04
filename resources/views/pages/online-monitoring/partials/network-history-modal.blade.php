<!-- History Popup Modal -->
<div id="networkHistoryModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 md:p-6">
    <!-- Dark Backdrop -->
    <div id="networkHistoryBackdrop" class="fixed inset-0 bg-black/60 transition-opacity backdrop-blur-[2px]"></div>

    <!-- Modal Dialog Window -->
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl z-10 overflow-hidden flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50/50">
            <div>
                <h3 class="text-lg md:text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="history" class="w-5 h-5 text-[#0079C6]"></i>
                    <span>Riwayat Monitoring & Gangguan Jaringan</span>
                </h3>
                <p id="historyModalLocationName" class="text-xs md:text-sm text-slate-500 font-medium mt-0.5">
                    Memuat informasi lokasi...
                </p>
            </div>
            <button type="button" 
                    onclick="closeNetworkHistoryModal()" 
                    class="text-slate-400 hover:text-slate-700 p-2 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer"
                    title="Tutup Modal">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Filter Bar -->
        <div class="px-6 py-3 bg-white border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs md:text-sm">
            <!-- Preset Buttons -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-slate-500 font-semibold mr-1">Filter Cepat:</span>
                <button type="button" 
                        onclick="applyHistoryPreset('24h')" 
                        id="preset24h"
                        class="px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-[#0079C6] transition-colors">
                    24 Jam
                </button>
                <button type="button" 
                        onclick="applyHistoryPreset('7d')" 
                        id="preset7d"
                        class="px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-[#0079C6] transition-colors">
                    7 Hari
                </button>
                <button type="button" 
                        onclick="applyHistoryPreset('30d')" 
                        id="preset30d"
                        class="px-2.5 py-1 rounded-md text-xs font-semibold bg-[#0079C6] text-white transition-colors">
                    30 Hari
                </button>
                <button type="button" 
                        onclick="applyHistoryPreset('all')" 
                        id="presetAll"
                        class="px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-[#0079C6] transition-colors">
                    Semua
                </button>
            </div>

            <!-- Custom Date Range Form -->
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex items-center gap-1.5">
                    <label for="historyStartDate" class="text-slate-500 text-xs font-medium">Dari:</label>
                    <input type="date" 
                           id="historyStartDate" 
                           class="px-2.5 py-1 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#0079C6]">
                </div>
                <div class="flex items-center gap-1.5">
                    <label for="historyEndDate" class="text-slate-500 text-xs font-medium">Sampai:</label>
                    <input type="date" 
                           id="historyEndDate" 
                           class="px-2.5 py-1 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#0079C6]">
                </div>
                <button type="button" 
                        onclick="applyHistoryCustomDate()" 
                        class="px-3 py-1 bg-[#0079C6] hover:bg-[#005f9e] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                    Filter
                </button>
                <button type="button" 
                        onclick="resetHistoryFilter()" 
                        class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition-colors">
                    Reset
                </button>
            </div>
        </div>

        <!-- Body / Content -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1">
            <!-- 3 Indicator Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Card 1: Total Downtime -->
                <div class="bg-gradient-to-br from-rose-50 to-orange-50 border border-rose-100 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Waktu Henti</span>
                        <span id="historyModalDowntimeIndicator" class="text-2xl font-bold text-rose-600 mt-1 block">0m</span>
                        <span id="historyModalPeriodLabel" class="text-[11px] text-slate-500 mt-0.5 block">Akumulasi periode</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                </div>

                <!-- Card 2: Uptime Network -->
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Uptime Ketersediaan</span>
                        <span id="historyModalUptimeIndicator" class="text-2xl font-bold text-[#00B050] mt-1 block">100%</span>
                        <span class="text-[11px] text-slate-500 mt-0.5 block">Rasio waktu aktif jaringan</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-[#00B050] flex items-center justify-center">
                        <i data-lucide="activity" class="w-6 h-6"></i>
                    </div>
                </div>

                <!-- Card 3: Total Incidents -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Frekuensi Gangguan</span>
                        <span id="historyModalIncidentIndicator" class="text-2xl font-bold text-slate-900 mt-1 block">0 Insiden</span>
                        <span class="text-[11px] text-slate-500 mt-0.5 block">Total status bermasalah</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-[#0079C6]/10 text-[#0079C6] flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <!-- Detailed History Table -->
            <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm flex flex-col">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="list" class="w-4 h-4 text-slate-500"></i>
                        <span>Catatan Log Riwayat & Pemulihan</span>
                    </span>
                    <span id="historyRecordCount" class="text-xs text-slate-500 font-medium">0 Data Ditemukan</span>
                </div>

                <div class="overflow-x-auto max-h-[380px] figma-scrollbar">
                    <table class="w-full text-center border-collapse">
                        <thead class="sticky top-0 bg-slate-100/90 backdrop-blur-xs z-10 border-b border-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-3 text-left">Waktu Monitoring</th>
                                <th class="py-3 px-3 text-center">Status Lalu</th>
                                <th class="py-3 px-3 text-center">Status Kini</th>
                                <th class="py-3 px-3 text-center">Awal Gangguan</th>
                                <th class="py-3 px-3 text-center">Akhir Gangguan</th>
                                <th class="py-3 px-3 text-center">Durasi</th>
                                <th class="py-3 px-3 text-center">Waktu Pulih</th>
                                <th class="py-3 px-4 text-left">Keterangan / Diagnostik</th>
                            </tr>
                        </thead>
                        <tbody id="historyModalTableBody" class="divide-y divide-slate-100 text-slate-800 text-xs">
                            <!-- Populated dynamically via JS API fetch -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between px-6 py-4 bg-slate-50 border-t border-slate-200">
            <span class="text-xs text-slate-400">
                Data divalidasi dari audit log server backend
            </span>
            <button type="button" 
                    onclick="closeNetworkHistoryModal()" 
                    class="px-5 py-2 bg-slate-200 hover:bg-slate-300 active:scale-95 text-slate-800 font-semibold text-xs md:text-sm rounded-lg transition-all cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>
