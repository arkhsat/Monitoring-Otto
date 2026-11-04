<!-- Modal Tambah / Edit Lokasi -->
<div id="lokasiModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <!-- Dark Backdrop -->
    <div id="lokasiBackdrop" class="fixed inset-0 bg-black/60 transition-opacity backdrop-blur-[1px]"></div>

    <!-- Modal Dialog Window -->
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl z-10 overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50/50">
            <h3 id="lokasiModalTitle" class="text-lg md:text-xl font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="map-pin" class="w-5 h-5 text-[#0079C6]"></i>
                <span>Tambah Lokasi Baru</span>
            </h3>
            <button type="button" onclick="closeLokasiModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form Error Alert -->
        <div id="lokasiFormError" class="hidden mx-6 mt-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-rose-500"></i>
            <span id="lokasiFormErrorText">-</span>
        </div>

        <!-- Form Body -->
        <form id="lokasiForm" onsubmit="handleLokasiFormSubmit(event)" class="p-6 overflow-y-auto space-y-4">
            <input type="hidden" id="lokasiEditId" value="">

            <!-- 1. Nama Lokasi -->
            <div>
                <label for="nama_lokasi" class="block text-sm font-bold text-slate-800 mb-1.5">
                    Nama Lokasi <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="nama_lokasi" 
                       name="nama_lokasi" 
                       required
                       placeholder="Contoh: True Blue, Gelael Tebet" 
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#0079C6] focus:ring-2 focus:ring-[#0079C6]/20 text-sm font-medium text-slate-900 outline-none transition-all">
            </div>

            <!-- 2. UUID Lokasi -->
            <div>
                <label for="uuid_lokasi" class="block text-sm font-bold text-slate-800 mb-1.5">
                    UUID Lokasi
                </label>
                <input type="text" 
                       id="uuid_lokasi" 
                       name="uuid_lokasi" 
                       placeholder="Masukkan UUID (opsional, kosongkan untuk auto-generate)" 
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#0079C6] focus:ring-2 focus:ring-[#0079C6]/20 text-sm font-medium text-slate-900 outline-none transition-all">
                <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                    <span>Tersimpan di database dalam bentuk hash otomatis.</span>
                </p>
            </div>

            <!-- 3. API Lokasi -->
            <div>
                <label for="api_lokasi" class="block text-sm font-bold text-slate-800 mb-1.5">
                    API Lokasi (Endpoint / Key)
                </label>
                <input type="text" 
                       id="api_lokasi" 
                       name="api_lokasi" 
                       placeholder="Masukkan API Key / URL Endpoint Lokasi" 
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#0079C6] focus:ring-2 focus:ring-[#0079C6]/20 text-sm font-medium text-slate-900 outline-none transition-all">
                <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                    <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-500"></i>
                    <span>Tersimpan di database dalam bentuk hash demi keamanan.</span>
                </p>
            </div>

            <!-- 4. Keterangan Alamat -->
            <div>
                <label for="keterangan_alamat" class="block text-sm font-bold text-slate-800 mb-1.5">
                    Keterangan Alamat
                </label>
                <textarea id="keterangan_alamat" 
                          name="keterangan_alamat" 
                          rows="3"
                          placeholder="Masukkan detail alamat lengkap lokasi..." 
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#0079C6] focus:ring-2 focus:ring-[#0079C6]/20 text-sm font-medium text-slate-900 outline-none transition-all resize-none"></textarea>
            </div>

            <!-- 5. Atur Batas Waktu Henti (Downtime Threshold) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="batas_waktu_henti" class="block text-sm font-bold text-slate-800">
                        Atur Batas Waktu Henti (Downtime Limit Threshold) <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-xs text-slate-400 font-medium">Dalam Detik</span>
                </div>
                <div class="relative">
                    <input type="number" 
                           id="batas_waktu_henti" 
                           name="batas_waktu_henti" 
                           required 
                           min="1" 
                           value="60"
                           placeholder="60" 
                           class="w-full pl-4 pr-16 py-2.5 rounded-xl border border-slate-300 focus:border-[#0079C6] focus:ring-2 focus:ring-[#0079C6]/20 text-sm font-medium text-slate-900 outline-none transition-all">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">
                        Detik
                    </span>
                </div>
                <!-- Helper Note -->
                <p class="text-xs text-slate-500 mt-2 bg-blue-50/70 border border-blue-100 p-2.5 rounded-lg flex items-start gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-[#0079C6] shrink-0 mt-0.5"></i>
                    <span>
                        Batas toleransi offline/tidak merespon API sebelum status jaringan dinyatakan <strong>Offline</strong> di dashboard. Selama masih dalam rentang batas ini, status tetap dianggap normal.
                    </span>
                </p>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 mt-6">
                <button type="button" 
                        onclick="closeLokasiModal()" 
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" 
                        id="btnSimpanLokasi"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#0079C6] hover:bg-[#005f9e] active:scale-95 text-white font-semibold text-sm rounded-xl shadow-md transition-all cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span id="btnSimpanLokasiText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Lokasi -->
<div id="deleteLokasiModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <div id="deleteLokasiBackdrop" class="fixed inset-0 bg-black/60 transition-opacity backdrop-blur-[1px]"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden flex flex-col p-6 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mb-4">
                <i data-lucide="trash-2" class="w-8 h-8"></i>
            </div>
            <h4 class="text-xl font-bold text-slate-900 mb-2">Hapus Lokasi?</h4>
            <p class="text-sm text-slate-600 mb-6">
                Apakah Anda yakin ingin menghapus lokasi <strong id="deleteLokasiNameText">-</strong>? Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>
        <div class="grid grid-cols-2 gap-3 w-full">
            <button type="button" 
                    onclick="closeDeleteLokasiModal()" 
                    class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" 
                    id="btnConfirmDeleteLokasi"
                    onclick="confirmDeleteLokasi()" 
                    class="w-full py-2.5 px-4 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-semibold text-sm rounded-xl shadow-sm transition-all cursor-pointer">
                Hapus
            </button>
        </div>
    </div>
</div>
