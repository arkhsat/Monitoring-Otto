<!-- Settlement Popup Modal -->
<div id="settlementModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <!-- Dark Backdrop -->
    <div id="settlementBackdrop" class="fixed inset-0 bg-black/60 transition-opacity backdrop-blur-[1px]"></div>

    <!-- Modal Dialog Window -->
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg md:text-xl font-bold text-slate-900">Settlement MDD</h3>
            <button type="button" onclick="closeSettlementModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Body / Content -->
        <div class="p-6 overflow-y-auto space-y-5">
            <!-- Details Summary Box -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                <div>
                    <span class="text-xs text-slate-500 block">Tanggal</span>
                    <span id="settlementModalDate" class="font-bold text-slate-800">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Admin Otto</span>
                    <span id="settlementModalAdmin" class="font-bold text-slate-800">Rp 900.000</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Settlement MDD</span>
                    <span id="settlementModalMdd" class="font-bold text-rose-600">Rp 960.000</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Report Lokasi</span>
                    <span id="settlementModalReport" class="font-bold text-slate-800">Rp 900.000</span>
                </div>
            </div>

            <!-- Settlement Preview / Information -->
            <div class="border border-slate-200 rounded-xl p-4 bg-white">
                <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                    <span class="text-sm font-semibold text-slate-800">Rincian Perbedaan Selisih</span>
                    <span class="text-xs font-bold text-[#FF0000] bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full">Tidak Cocok</span>
                </div>
                <div class="space-y-2 text-sm text-slate-600">
                    <div class="flex justify-between">
                        <span>Total Transaksi Tercatat:</span>
                        <span class="font-medium text-slate-800">Rp 960.000</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Selisih dengan Admin Otto:</span>
                        <span class="font-bold text-rose-600">+ Rp 60.000</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Status Rekonsiliasi:</span>
                        <span class="font-medium text-amber-600">Menunggu Verifikasi Settlement</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Actions (PDF & Send Settlement) -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-200">
            <button type="button" 
                    id="btnSettlementPdf" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-sm rounded-lg shadow-sm transition-all">
                <i data-lucide="file-text" class="w-4 h-4 text-rose-500"></i>
                <span>PDF</span>
            </button>
            <button type="button" 
                    id="btnOpenConfirmModal" 
                    onclick="openConfirmModal()"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-[#0079C6] hover:bg-[#005f9e] active:scale-95 text-white font-semibold text-sm rounded-lg shadow-sm transition-all">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>Send Settlement</span>
            </button>
        </div>
    </div>
</div>

<!-- Confirmation Popup Modal -->
<div id="confirmModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <!-- Dark Backdrop for confirmation -->
    <div id="confirmBackdrop" class="fixed inset-0 bg-black/60 transition-opacity backdrop-blur-[1px]"></div>

    <!-- Confirmation Dialog Window -->
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden flex flex-col p-6 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-[#0079C6] flex items-center justify-center mb-4">
                <i data-lucide="help-circle" class="w-8 h-8"></i>
            </div>
            <h4 class="text-xl font-bold text-slate-900 mb-2">Kirim Settlement MDD?</h4>
            <p class="text-sm text-slate-600 mb-6">
                Apakah Anda yakin ingin mengirim data settlement ini untuk diproses lebih lanjut?
            </p>
        </div>

        <!-- Buttons: Batal & Kirim Settlement -->
        <div class="grid grid-cols-2 gap-3 w-full">
            <button type="button" 
                    onclick="closeConfirmModal()" 
                    class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-lg transition-colors">
                Batal
            </button>
            <button type="button" 
                    id="btnKirimSettlementAction"
                    onclick="handleKirimSettlement()" 
                    class="w-full py-2.5 px-4 bg-[#0079C6] hover:bg-[#005f9e] active:scale-95 text-white font-semibold text-sm rounded-lg shadow-sm transition-all">
                Kirim Settlement
            </button>
        </div>
    </div>
</div>

