<!-- ============================================================
     Settlement Popup Modal
     Logos: public/images/banks/{bca,mandiri,bri,bni}.png
     ============================================================ -->
<div id="settlementModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <!-- Dark Backdrop (click to close) -->
    <div id="settlementBackdrop" class="fixed inset-0 bg-black/60 transition-opacity backdrop-blur-[1px]"></div>

    <!-- Card with blue border -->
    <div class="relative z-10 w-full max-w-md max-h-[92vh] flex flex-col bg-[#0079C6] rounded-2xl p-[5px] shadow-2xl">
        <div class="flex flex-col min-h-0 bg-white rounded-xl overflow-hidden">

            <!-- Tanggal -->
            <div class="px-5 pt-4 pb-3">
                <h3 class="text-xl font-extrabold text-black leading-tight">Tanggal</h3>
                <p id="settlementModalDate" class="text-sm font-semibold text-slate-400">-</p>
            </div>

            <!-- Blue divider -->
            <div class="h-[5px] bg-[#0079C6]"></div>

            <!-- Table -->
            <div class="px-6 pt-4 flex flex-col min-h-0">
                <div
                    class="grid grid-cols-3 text-center font-extrabold text-sm text-black pb-2 border-b border-slate-300">
                    <div>Bank</div>
                    <div>Qty</div>
                    <div>Nominal</div>
                </div>

                <!-- Scrollable bank list -->
                <div id="settlementBankList"
                    class="overflow-y-auto min-h-[200px] max-h-[360px] py-2 [scrollbar-width:thin]">
                    <!-- Rendered by renderSettlementData() -->
                </div>

                <!-- Grand total -->
                <div class="grid grid-cols-3 items-center border-t border-slate-300 py-2">
                    <div></div>
                    <div></div>
                    <div class="text-center text-sm font-extrabold text-black">Total</div>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-300 pb-2">
                    <div></div>
                    <div></div>
                    <div id="settlementGrandTotal" class="text-center text-sm font-extrabold text-black">0</div>
                </div>

                <!-- File Settlement (.txt & .ok) -->
                <div class="px-2 py-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-extrabold text-black">File Settlement</span>
                        <span id="settlementFilesStatus" class="text-[11px] font-medium text-slate-400">-</span>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5 space-y-2 text-xs">
                        <!-- Row .txt -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <i data-lucide="file-text" class="w-4 h-4 text-[#0079C6] shrink-0"></i>
                                <span class="font-bold text-slate-700 shrink-0">.txt:</span>
                                <span id="settlementTxtName" class="text-slate-600 truncate">Belum tersedia</span>
                            </div>
                            <div id="settlementTxtActions" class="hidden flex items-center gap-1.5 shrink-0">
                                <button type="button" onclick="previewSettlementFile('txt')"
                                    class="px-2 py-1 rounded bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold cursor-pointer">
                                    Lihat
                                </button>
                                <a id="settlementTxtDownload" href="#" target="_blank" rel="noopener"
                                    class="px-2 py-1 rounded bg-[#0079C6] hover:bg-[#005f9e] text-white font-semibold">
                                    Download
                                </a>
                            </div>
                        </div>

                        <!-- Row .ok -->
                        <div class="flex items-center justify-between gap-2 border-t border-slate-200/80 pt-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <i data-lucide="file-check-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                <span class="font-bold text-slate-700 shrink-0">.ok:</span>
                                <span id="settlementOkName" class="text-slate-600 truncate">Belum tersedia</span>
                            </div>
                            <div id="settlementOkActions" class="hidden flex items-center gap-1.5 shrink-0">
                                <button type="button" onclick="previewSettlementFile('ok')"
                                    class="px-2 py-1 rounded bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold cursor-pointer">
                                    Lihat
                                </button>
                                <a id="settlementOkDownload" href="#" target="_blank" rel="noopener"
                                    class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Isi File (.txt / .ok) -->
                    <div id="settlementFilePreviewBox" class="hidden bg-slate-900 text-slate-100 rounded-lg p-2.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <span id="settlementPreviewTitle" class="text-[11px] font-bold text-sky-300 truncate">-</span>
                            <button type="button" onclick="closeSettlementFilePreview()"
                                class="text-[11px] text-slate-300 hover:text-white cursor-pointer">
                                Tutup
                            </button>
                        </div>
                        <pre id="settlementFilePreviewContent"
                            class="text-[11px] leading-relaxed font-mono max-h-36 overflow-auto whitespace-pre-wrap break-all [scrollbar-width:thin]"></pre>
                    </div>
                </div>

                <!-- Send Settlement (red) -->
                <div class="mx-6 mb-3">
                    <button id="btnOpenConfirmModal"
                        onclick="openConfirmModal()"
                        class="w-full py-2.5 bg-[#FF0000] hover:bg-[#d90000] active:brightness-90 text-white font-bold text-sm rounded-lg transition-colors cursor-pointer">
                        Send Settlement
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================================
     Confirmation Popup Modal
     ============================================================ -->
        <div id="confirmModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
            <!-- Dark Backdrop (click to close) -->
            <div id="confirmBackdrop" class="fixed inset-0 bg-black/60 transition-opacity backdrop-blur-[1px]"></div>

            <div class="relative z-10 w-full max-w-lg bg-[#0079C6] rounded-2xl p-[5px] shadow-2xl">
                <div class="bg-white rounded-xl px-8 pt-14 pb-5">
                    <h4 class="text-center text-3xl font-extrabold text-black leading-snug mb-12">
                        Sudah Yakin dan Ingin<br>Kirim Settlement?
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="closeConfirmModal()"
                            class="py-2 bg-[#F00000] hover:bg-[#c80000] text-white font-bold text-sm rounded-md transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="button" id="btnKirimSettlementAction" onclick="handleKirimSettlement()"
                            class="py-2 bg-[#3DF500] hover:bg-[#33cc00] text-white font-bold text-sm rounded-md transition-colors cursor-pointer">
                            Kirim Settlement
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // ---------- Config ----------
            const SETTLEMENT_LOGO_BASE = "{{ asset('images/banks') }}";
            const SETTLEMENT_THOUSAND_SEPARATOR = true; // mockup shows plain digits (100000). Set true for 100.000

            // Fixed display order + logos
            const SETTLEMENT_BANKS = [{
                    key: 'bca',
                    name: 'BCA',
                    logo: 'BCA.png'
                },
                {
                    key: 'mandiri',
                    name: 'Mandiri',
                    logo: 'Bank Mandiri.png'
                },
                {
                    key: 'bri',
                    name: 'BRI',
                    logo: 'Bank BRI.png'
                },
                {
                    key: 'bni',
                    name: 'BNI',
                    logo: 'Bank BNI.png'
                },
            ];

            // Data Settlement (dikosongkan terlebih dahulu untuk diisi otomatis dari database nantinya)
            // Struktur: { date, file: {name, url}, banks: { bca: [{qty, nominal}], mandiri: [], bri: [], bni: [] } }
            const Settlement = {
                date: null,
                file: {
                    name: null,
                    url: '#'
                },
                banks: {
                    bca: [],
                    mandiri: [],
                    bri: [],
                    bni: [],
                },
            };

            // ---------- Helpers ----------
            function fmtSettlement(n) {
                return SETTLEMENT_THOUSAND_SEPARATOR ? Number(n).toLocaleString('id-ID') : String(n);
            }

            function escSettlement(s) {
                return String(s ?? '').replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
            }

            // Subtotal = sum(qty * nominal); Grand total = sum of subtotals
            function renderSettlementData(data = Settlement) {
                const list = document.getElementById('settlementBankList');
                let grand = 0;

                const bankRowsHtml = SETTLEMENT_BANKS.map(bank => {
                    const lines = (data && data.banks && data.banks[bank.key]) || [];
                    if (!lines.length) return '';
                    const subtotal = lines.reduce((s, l) => s + Number(l.qty) * Number(l.nominal), 0);
                    grand += subtotal;

                    const qtyCol = lines.map(l =>
                        `<div class="italic text-xs text-slate-700">${fmtSettlement(l.qty)}</div>`).join('');
                    const nomCol = lines.map(l =>
                        `<div class="text-xs text-slate-700">${fmtSettlement(l.nominal)}</div>`).join('');

                    return `
                <div class="grid grid-cols-3 items-center py-4">
                    <div class="flex justify-center">
                        <img src="${SETTLEMENT_LOGO_BASE}/${bank.logo}" alt="${bank.name}" class="h-10 w-24 object-contain">
                    </div>
                    <div class="text-center space-y-0.5">
                        ${qtyCol}
                        <div class="text-xs font-extrabold text-black pt-0.5">Subtotal</div>
                    </div>
                    <div class="text-center space-y-0.5">
                        ${nomCol}
                        <div class="text-xs font-extrabold text-black pt-0.5">${fmtSettlement(subtotal)}</div>
                    </div>
                </div>`;
                }).join('');

                if (!bankRowsHtml.trim()) {
                    list.innerHTML = `
                        <div class="flex flex-col items-center justify-center py-10 text-slate-400 text-xs text-center">
                            <i data-lucide="receipt" class="w-8 h-8 text-slate-300 mb-1.5"></i>
                            <span>Belum ada data rincian transaksi bank untuk settlement ini.</span>
                        </div>
                    `;
                } else {
                    list.innerHTML = bankRowsHtml;
                }

                document.getElementById('settlementGrandTotal').textContent = fmtSettlement(grand);
                if (data && data.date) document.getElementById('settlementModalDate').textContent = data.date;

                if (window.lucide) lucide.createIcons();
            }

            const SETTLEMENTS_API_URL = @json(url('/api/settlements'));
            const SETTLEMENTS_DOWNLOAD_URL = @json(url('/api/settlements/download'));

            let currentSettlementFiles = { txt: null, ok: null };
            let currentSettlementLokasiId = null;
            let currentSettlementIsoDate = null;

            function resolveSettlementIsoDate(data) {
                if (data?.iso_date && /^\d{4}-\d{2}-\d{2}$/.test(data.iso_date)) {
                    return data.iso_date;
                }
                const raw = String(data?.date || '').trim();
                const ddmmyyyy = raw.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
                if (ddmmyyyy) {
                    return `${ddmmyyyy[3]}-${ddmmyyyy[2]}-${ddmmyyyy[1]}`;
                }
                return null;
            }

            function resolveSettlementLokasiId(data) {
                if (data?.kode) return Number(data.kode);
                const btn = document.getElementById('locationDropdownBtn');
                return Number(btn?.getAttribute('data-current-kode') || 1);
            }

            function buildSettlementDownloadUrl(type) {
                if (!currentSettlementLokasiId || !currentSettlementIsoDate) return '#';
                return `${SETTLEMENTS_DOWNLOAD_URL}?lokasi_id=${encodeURIComponent(currentSettlementLokasiId)}&tanggal_settlement=${encodeURIComponent(currentSettlementIsoDate)}&type=${encodeURIComponent(type)}`;
            }

            function renderSingleSettlementFile(type, fileInfo) {
                const cap = type === 'txt' ? 'Txt' : 'Ok';
                const nameEl = document.getElementById(`settlement${cap}Name`);
                const actionsEl = document.getElementById(`settlement${cap}Actions`);
                const downloadEl = document.getElementById(`settlement${cap}Download`);

                if (fileInfo && fileInfo.exists) {
                    nameEl.textContent = fileInfo.name || `settlement.${type}`;
                    nameEl.classList.remove('text-slate-400');
                    nameEl.classList.add('text-slate-800', 'font-medium');
                    downloadEl.href = buildSettlementDownloadUrl(type);
                    actionsEl.classList.remove('hidden');
                } else {
                    nameEl.textContent = `Belum ada file .${type}`;
                    nameEl.classList.remove('text-slate-800', 'font-medium');
                    nameEl.classList.add('text-slate-400');
                    downloadEl.href = '#';
                    actionsEl.classList.add('hidden');
                }
            }

            function closeSettlementFilePreview() {
                document.getElementById('settlementFilePreviewBox').classList.add('hidden');
                document.getElementById('settlementFilePreviewContent').textContent = '';
            }

            async function previewSettlementFile(type) {
                const fileInfo = currentSettlementFiles[type];
                if (!fileInfo || !fileInfo.exists) return;

                const previewBox = document.getElementById('settlementFilePreviewBox');
                const previewTitle = document.getElementById('settlementPreviewTitle');
                const previewContent = document.getElementById('settlementFilePreviewContent');

                previewTitle.textContent = fileInfo.name || `settlement.${type}`;
                previewContent.textContent = 'Memuat isi file...';
                previewBox.classList.remove('hidden');

                if (fileInfo.content !== null && fileInfo.content !== undefined) {
                    previewContent.textContent = fileInfo.content !== ''
                        ? fileInfo.content
                        : `(File .${type} kosong / 0 byte)`;
                    return;
                }

                const downloadUrl = buildSettlementDownloadUrl(type);
                if (downloadUrl !== '#') {
                    try {
                        const res = await fetch(`${downloadUrl}&inline=1`, {
                            headers: { 'Accept': 'text/plain' }
                        });
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        const text = await res.text();
                        previewContent.textContent = text !== '' ? text : `(File .${type} kosong / 0 byte)`;
                    } catch (err) {
                        previewContent.textContent = `Gagal memuat isi file .${type}: ${err.message}`;
                    }
                }
            }

            async function loadSettlementFiles(lokasiId, isoDate) {
                const statusEl = document.getElementById('settlementFilesStatus');
                closeSettlementFilePreview();
                currentSettlementFiles = { txt: null, ok: null };
                currentSettlementLokasiId = lokasiId;
                currentSettlementIsoDate = isoDate;
                renderSingleSettlementFile('txt', null);
                renderSingleSettlementFile('ok', null);

                if (!lokasiId || !isoDate) {
                    statusEl.textContent = 'Lokasi/tanggal tidak diketahui';
                    return;
                }

                statusEl.textContent = `Memuat file (Lokasi #${lokasiId}, ${isoDate})...`;

                try {
                    const url = `${SETTLEMENTS_API_URL}?lokasi_id=${encodeURIComponent(lokasiId)}&tanggal_settlement=${encodeURIComponent(isoDate)}`;
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) {
                        statusEl.textContent = `Gagal memuat file (HTTP ${res.status})`;
                        return;
                    }

                    const json = await res.json();

                    if (!json.success || !json.data) {
                        statusEl.textContent = `Belum ada file (Lokasi #${lokasiId}, ${isoDate})`;
                        return;
                    }

                    currentSettlementFiles.txt = json.data.file_txt || null;
                    currentSettlementFiles.ok = json.data.file_ok || null;

                    renderSingleSettlementFile('txt', currentSettlementFiles.txt);
                    renderSingleSettlementFile('ok', currentSettlementFiles.ok);

                    const hasBoth = Boolean(currentSettlementFiles.txt?.exists && currentSettlementFiles.ok?.exists);
                    const hasAny = Boolean(currentSettlementFiles.txt?.exists || currentSettlementFiles.ok?.exists);
                    statusEl.textContent = hasBoth ? 'File .txt & .ok tersedia' : (hasAny ? 'Sebagian file tersedia' : `Belum ada file (Lokasi #${lokasiId}, ${isoDate})`);
                } catch (err) {
                    statusEl.textContent = 'Gagal memuat file settlement';
                }
            }

            // ---------- Open / close ----------
            function openSettlementModal(data) {
                const modalPayload = (data && data.banks)
                    ? data
                    : { ...Settlement, date: data?.date || Settlement.date };

                renderSettlementData(modalPayload);
                document.getElementById('settlementModal').classList.remove('hidden');
                if (window.lucide) lucide.createIcons();

                const lokasiId = resolveSettlementLokasiId(data);
                const isoDate = resolveSettlementIsoDate(data);
                loadSettlementFiles(lokasiId, isoDate);
            }

            function closeSettlementModal() {
                closeSettlementFilePreview();
                document.getElementById('settlementModal').classList.add('hidden');
            }

            function openConfirmModal() {
                document.getElementById('confirmModal').classList.remove('hidden');
            }

            function closeConfirmModal() {
                document.getElementById('confirmModal').classList.add('hidden');
            }

            // TODO (backend): POST subtotals + .txt file to the endpoint that forwards to the 3rd-party API
            function handleKirimSettlement() {
                closeConfirmModal();
                closeSettlementModal();
            }

            // Render awal daftar settlement dengan objek kosong
            renderSettlementData(Settlement);

            // Click backdrop (or the empty area around the card) to exit
            document.getElementById('settlementModal').addEventListener('click', e => {
                if (e.target.id === 'settlementModal' || e.target.id === 'settlementBackdrop') closeSettlementModal();
            });
            document.getElementById('confirmModal').addEventListener('click', e => {
                if (e.target.id === 'confirmModal' || e.target.id === 'confirmBackdrop') closeConfirmModal();
            });

            // Esc closes the top-most popup
            document.addEventListener('keydown', e => {
                if (e.key !== 'Escape') return;
                if (!document.getElementById('confirmModal').classList.contains('hidden')) closeConfirmModal();
                else closeSettlementModal();
            });
        </script>
