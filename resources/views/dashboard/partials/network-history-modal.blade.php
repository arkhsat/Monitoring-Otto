<!-- History Popup Modal -->
<div id="networkHistoryModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <!-- Dark Backdrop -->
    <div id="networkHistoryBackdrop" class="fixed inset-0 bg-black/60 transition-opacity backdrop-blur-[1px]"></div>

    <!-- Modal Dialog Window -->
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
            <div>
                <h3 class="text-lg md:text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="history" class="w-5 h-5 text-[#0079C6]"></i>
                    <span>Riwayat Jaringan Lokasi</span>
                </h3>
                <p id="historyModalLocationName" class="text-xs md:text-sm text-slate-500 font-medium mt-0.5">-</p>
            </div>
            <button type="button" onclick="closeNetworkHistoryModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Body / Content -->
        <div class="p-6 overflow-y-auto space-y-6">
            <!-- 2 Indicator Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Indicator Card 1: Total Downtime (30 Hari) -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Waktu Henti</span>
                        <span id="historyModalDowntimeIndicator" class="text-2xl font-bold text-slate-900 mt-1 block">37m</span>
                        <span class="text-[11px] text-slate-500 mt-0.5 block">Periode 30 hari terakhir</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-[#0079C6]/10 text-[#0079C6] flex items-center justify-center">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                </div>

                <!-- Indicator Card 2: Uptime Network -->
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Uptime Jaringan</span>
                        <span id="historyModalUptimeIndicator" class="text-2xl font-bold text-[#00B050] mt-1 block">99.8%</span>
                        <span class="text-[11px] text-slate-500 mt-0.5 block">Ketersediaan koneksi stabil</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-[#00B050] flex items-center justify-center">
                        <i data-lucide="activity" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <!-- 3 Column Table: Tanggal, Waktu Henti (Downtime), Status -->
            <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-800">Catatan Riwayat Harian</span>
                    <span class="text-xs text-slate-500">5 Hari Terakhir</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-center border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 bg-white text-slate-700 font-bold text-xs md:text-sm">
                                <th class="py-3 px-4 text-center">Tanggal</th>
                                <th class="py-3 px-4 text-center">Waktu Henti (Downtime)</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="historyModalTableBody" class="divide-y divide-slate-100 text-slate-800 text-xs md:text-sm">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end px-6 py-4 bg-slate-50 border-t border-slate-200">
            <button type="button" 
                    onclick="closeNetworkHistoryModal()" 
                    class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold text-sm rounded-lg transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

