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

                <!-- File Settlement (.txt) -->
                <div class="flex items-center gap-4 px-6 py-3">
                    <span class="text-sm font-extrabold text-black">File Settlement</span>
                    <a id="settlementFileLink" href="#" target="_blank" rel="noopener"
                        class="text-[#0079C6] hover:text-[#005f9e] transition-colors" title="File settlement (.txt)">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </a>
                    <span id="settlementFileName" class="text-xs text-slate-400 truncate"></span>
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
            const SETTLEMENT_THOUSAND_SEPARATOR = false; // mockup shows plain digits (100000). Set true for 100.000

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

            // Placeholder data. Replace with backend data later:
            // { date, file: {name, url}, banks: { bca: [{qty, nominal}, ...], ... } }
            const SETTLEMENT_SAMPLE = {
                date: '22/10/2026',
                file: {
                    name: 'settlement.txt',
                    url: '#'
                },
                banks: {
                    bca: [{
                        qty: 1,
                        nominal: 100000
                    }, {
                        qty: 1,
                        nominal: 100000
                    }],
                    mandiri: [{
                        qty: 2,
                        nominal: 200000
                    }, {
                        qty: 1,
                        nominal: 100000
                    }],
                    bri: [{
                        qty: 3,
                        nominal: 300000
                    }, {
                        qty: 3,
                        nominal: 100000
                    }],
                    bni: [{
                        qty: 4,
                        nominal: 350000
                    }],
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
            function renderSettlementData(data = SETTLEMENT_SAMPLE) {
                const list = document.getElementById('settlementBankList');
                let grand = 0;

                list.innerHTML = SETTLEMENT_BANKS.map(bank => {
                    const lines = (data.banks && data.banks[bank.key]) || [];
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

                document.getElementById('settlementGrandTotal').textContent = fmtSettlement(grand);
                if (data.date) document.getElementById('settlementModalDate').textContent = data.date;

                const link = document.getElementById('settlementFileLink');
                link.href = (data.file && data.file.url) || '#';
                document.getElementById('settlementFileName').textContent = (data.file && data.file.name) || '';

                if (window.lucide) lucide.createIcons();
            }

            // ---------- Open / close ----------
            function openSettlementModal(data) {
                renderSettlementData(data || SETTLEMENT_SAMPLE);
                document.getElementById('settlementModal').classList.remove('hidden');
                if (window.lucide) lucide.createIcons();
            }

            function closeSettlementModal() {
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

            // ---------- Dummy preview ----------
            // The dashboard opens this popup by itself (it only sets the date), so fill the list
            // with dummy data on load and whenever the popup is shown with an empty list.
            // The date is left alone so the dashboard's own value stays. Remove once real data is wired in.
            const SETTLEMENT_PREVIEW = {
                ...SETTLEMENT_SAMPLE,
                date: null
            };
            renderSettlementData(SETTLEMENT_PREVIEW);

            new MutationObserver(() => {
                const modal = document.getElementById('settlementModal');
                const list = document.getElementById('settlementBankList');
                if (!modal.classList.contains('hidden') && !list.children.length) {
                    renderSettlementData(SETTLEMENT_PREVIEW);
                }
            }).observe(document.getElementById('settlementModal'), {
                attributes: true,
                attributeFilter: ['class']
            });

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
