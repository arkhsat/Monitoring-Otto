@extends('layouts.app', [
    'title' => 'Manajemen Lokasi - System Monitoring Otto',
    'headerTitle' => 'Lokasi'
])

@section('content')
    <!-- Toast Notification Container -->
    <div id="toastNotification" class="hidden fixed top-5 right-5 z-[70] max-w-md bg-white border border-slate-200 shadow-xl rounded-xl p-4 flex items-center gap-3 transition-all duration-300 animate-in fade-in slide-in-from-top-4">
        <div id="toastIconContainer" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <i data-lucide="check" class="w-5 h-5"></i>
        </div>
        <div class="flex-1">
            <h5 id="toastTitle" class="text-xs font-bold text-slate-900">Sukses</h5>
            <p id="toastMessage" class="text-xs text-slate-500 mt-0.5">-</p>
        </div>
        <button type="button" onclick="hideToast()" class="text-slate-400 hover:text-slate-600 p-1">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <!-- Main Table Section for Location Management -->
    <section class="bg-white rounded-xl shadow-lg p-4 md:p-6 flex-1 flex flex-col min-h-[500px]">
        
        <!-- Action Bar: Title, Search, & "Tambah Lokasi" Button -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg md:text-xl font-bold text-black tracking-tight flex items-center gap-2">
                    <span>Daftar Lokasi Onsite</span>
                    <span id="lokasiCountBadge" class="text-xs px-2.5 py-0.5 rounded-full bg-blue-50 text-[#0079C6] font-bold border border-blue-200">
                        {{ count($lokasis ?? []) }} Lokasi
                    </span>
                </h2>
                <p class="text-xs md:text-sm text-slate-500">
                    Kelola data master lokasi onsite, UUID, dan API Key lokasi (tersimpan otomatis dalam bentuk Hash via RESTful API)
                </p>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto flex-wrap">
                <!-- Search Bar -->
                <div class="relative flex-1 sm:w-64">
                    <input type="text" 
                           id="lokasiSearchInput" 
                           oninput="onSearchInput(this.value)"
                           placeholder="Cari lokasi atau alamat..." 
                           class="w-full pl-9 pr-4 py-2 text-xs md:text-sm rounded-xl border border-slate-300 focus:border-[#0079C6] focus:ring-2 focus:ring-[#0079C6]/20 outline-none transition-all">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>

                <!-- Refresh Button -->
                <button type="button" 
                        onclick="fetchLokasiData()" 
                        title="Segarkan data dari API" 
                        class="p-2.5 text-slate-600 hover:text-[#0079C6] hover:bg-slate-100 rounded-xl transition-colors cursor-pointer border border-slate-200">
                    <i data-lucide="refresh-cw" class="w-4 h-4" id="btnRefreshIcon"></i>
                </button>

                <!-- Button Tambah Lokasi -->
                <button type="button" 
                        onclick="openCreateLokasiModal()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#0079C6] hover:bg-[#005f9e] active:scale-95 text-white font-bold text-xs md:text-sm rounded-xl shadow-md transition-all cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah Lokasi</span>
                </button>
            </div>
        </div>

        <!-- Scrollable Table Container -->
        <div class="overflow-y-auto overflow-x-auto figma-scrollbar max-h-[620px] pr-2">
            <table class="w-full text-center border-collapse">
                <!-- Sticky Table Head -->
                <thead class="sticky top-0 bg-white z-10">
                    <tr class="border-b-2 border-slate-900 text-black font-bold text-xs md:text-sm">
                        <th class="py-3 px-4 text-left">Nama Lokasi</th>
                        <th class="py-3 px-4 text-left">UUID Lokasi (Hash DB)</th>
                        <th class="py-3 px-4 text-left">API Lokasi (Hash DB)</th>
                        <th class="py-3 px-4 text-left">Keterangan Alamat</th>
                        <th class="py-3 px-4 text-center">Batas Waktu Henti</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody id="lokasiTableBody" class="divide-y divide-slate-100 text-slate-800 text-xs md:text-sm">
                    @forelse ($lokasis ?? [] as $lokasi)
                        <tr data-id="{{ $lokasi->id }}" class="hover:bg-slate-50 transition-colors">
                            <!-- Nama Lokasi -->
                            <td class="py-4 px-4 text-left font-bold text-slate-900">
                                {{ $lokasi->nama_lokasi }}
                            </td>

                            <!-- UUID Lokasi (Hash DB) -->
                            <td class="py-4 px-4 text-left font-mono text-[11px] text-slate-600 max-w-[180px]">
                                @if($lokasi->uuid_lokasi)
                                    <span class="inline-block bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200 truncate max-w-[170px]" title="{{ $lokasi->uuid_lokasi }}">
                                        {{ $lokasi->uuid_lokasi }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic font-sans">-</span>
                                @endif
                            </td>

                            <!-- API Lokasi (Hash DB) -->
                            <td class="py-4 px-4 text-left font-mono text-[11px] text-slate-600 max-w-[180px]">
                                @if($lokasi->api_lokasi)
                                    <span class="inline-block bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200 truncate max-w-[170px]" title="{{ $lokasi->api_lokasi }}">
                                        {{ $lokasi->api_lokasi }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic font-sans">-</span>
                                @endif
                            </td>

                            <!-- Keterangan Alamat -->
                            <td class="py-4 px-4 text-left font-normal text-slate-600 max-w-[240px] truncate">
                                {{ $lokasi->keterangan_alamat ?: '-' }}
                            </td>

                            <!-- Batas Waktu Henti (Downtime Threshold) -->
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-[#0079C6] font-bold text-xs rounded-lg border border-blue-100">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    <span>{{ $lokasi->batas_waktu_henti }} Detik</span>
                                </span>
                            </td>

                            <!-- Aksi (Edit & Delete) -->
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Edit Button -->
                                    <button type="button" 
                                            onclick='openEditLokasiModal(@json($lokasi))'
                                            class="p-2 text-slate-600 hover:text-[#0079C6] hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                            title="Edit Lokasi">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" 
                                            onclick="openDeleteLokasiModal('{{ $lokasi->id }}', '{{ addslashes($lokasi->nama_lokasi) }}')"
                                            class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Hapus Lokasi">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyLokasiRow">
                            <td colspan="6" class="py-12 text-center text-slate-400 font-medium">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                    <span>Belum ada lokasi terdaftar. Silakan klik "Tambah Lokasi".</span>
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
    {{-- Lokasi Modal Form & Delete Confirmation --}}
    @include('pages.lokasi.partials.lokasi-modal')
@endsection

@push('scripts')
<script>
    // =========================================================================
    // RESTful API Client for Lokasi Management
    // =========================================================================

    const lokasiTableBody = document.getElementById('lokasiTableBody');
    const lokasiCountBadge = document.getElementById('lokasiCountBadge');
    const btnRefreshIcon = document.getElementById('btnRefreshIcon');
    const toastNotification = document.getElementById('toastNotification');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    const toastIconContainer = document.getElementById('toastIconContainer');

    const lokasiModal = document.getElementById('lokasiModal');
    const lokasiBackdrop = document.getElementById('lokasiBackdrop');
    const lokasiModalTitle = document.getElementById('lokasiModalTitle');
    const lokasiEditId = document.getElementById('lokasiEditId');
    const inputNamaLokasi = document.getElementById('nama_lokasi');
    const inputUuidLokasi = document.getElementById('uuid_lokasi');
    const inputApiLokasi = document.getElementById('api_lokasi');
    const inputKeteranganAlamat = document.getElementById('keterangan_alamat');
    const inputBatasWaktuHenti = document.getElementById('batas_waktu_henti');
    const btnSimpanLokasiText = document.getElementById('btnSimpanLokasiText');
    const lokasiFormError = document.getElementById('lokasiFormError');
    const lokasiFormErrorText = document.getElementById('lokasiFormErrorText');

    const deleteLokasiModal = document.getElementById('deleteLokasiModal');
    const deleteLokasiBackdrop = document.getElementById('deleteLokasiBackdrop');
    const deleteLokasiNameText = document.getElementById('deleteLokasiNameText');
    const btnConfirmDeleteLokasi = document.getElementById('btnConfirmDeleteLokasi');

    let deletingLocationId = null;
    let searchDebounceTimer = null;
    let currentLokasiDataset = [];

    /**
     * Show toast notification.
     */
    function showToast(title, message, isSuccess = true) {
        if (!toastNotification) return;

        toastTitle.textContent = title;
        toastMessage.textContent = message;

        if (isSuccess) {
            toastIconContainer.className = 'w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0';
            toastIconContainer.innerHTML = '<i data-lucide="check" class="w-5 h-5"></i>';
        } else {
            toastIconContainer.className = 'w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0';
            toastIconContainer.innerHTML = '<i data-lucide="alert-circle" class="w-5 h-5"></i>';
        }

        toastNotification.classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();

        setTimeout(() => {
            hideToast();
        }, 4000);
    }

    function hideToast() {
        if (toastNotification) toastNotification.classList.add('hidden');
    }

    /**
     * Fetch list of locations from GET /api/lokasi.
     */
    async function fetchLokasiData(search = '') {
        if (btnRefreshIcon) btnRefreshIcon.classList.add('animate-spin');

        let url = '/api/lokasi';
        if (search) {
            url += `?search=${encodeURIComponent(search)}`;
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP Error ${response.status}`);
            }

            const json = await response.json();
            if (json.success && json.data) {
                currentLokasiDataset = json.data;
                renderLokasiTable(json.data);
                if (lokasiCountBadge) {
                    lokasiCountBadge.textContent = `${json.data.length} Lokasi`;
                }
            }
        } catch (err) {
            console.error('Failed to fetch lokasi from API:', err);
            showToast('Gagal Mengambil Data', 'Tidak dapat terhubung ke RESTful API Lokasi', false);
        } finally {
            if (btnRefreshIcon) btnRefreshIcon.classList.remove('animate-spin');
        }
    }

    /**
     * Render locations dataset into table DOM.
     */
    function renderLokasiTable(items) {
        if (!lokasiTableBody) return;

        if (!items || items.length === 0) {
            lokasiTableBody.innerHTML = `
                <tr id="emptyLokasiRow">
                    <td colspan="6" class="py-12 text-center text-slate-400 font-medium">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                            <span>Tidak ada lokasi yang cocok atau terdaftar.</span>
                        </div>
                    </td>
                </tr>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return;
        }

        lokasiTableBody.innerHTML = items.map(lokasi => {
            const safeItem = JSON.stringify(lokasi).replace(/"/g, '&quot;');
            const safeName = (lokasi.nama_lokasi || '').replace(/'/g, "\\'");

            const uuidHtml = lokasi.uuid_lokasi
                ? `<span class="inline-block bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200 truncate max-w-[170px]" title="${lokasi.uuid_lokasi}">${lokasi.uuid_lokasi}</span>`
                : `<span class="text-slate-400 italic font-sans">-</span>`;

            const apiHtml = lokasi.api_lokasi
                ? `<span class="inline-block bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200 truncate max-w-[170px]" title="${lokasi.api_lokasi}">${lokasi.api_lokasi}</span>`
                : `<span class="text-slate-400 italic font-sans">-</span>`;

            return `
                <tr data-id="${lokasi.id}" class="hover:bg-slate-50 transition-colors animate-in fade-in duration-150">
                    <td class="py-4 px-4 text-left font-bold text-slate-900">
                        ${lokasi.nama_lokasi}
                    </td>
                    <td class="py-4 px-4 text-left font-mono text-[11px] text-slate-600 max-w-[180px]">
                        ${uuidHtml}
                    </td>
                    <td class="py-4 px-4 text-left font-mono text-[11px] text-slate-600 max-w-[180px]">
                        ${apiHtml}
                    </td>
                    <td class="py-4 px-4 text-left font-normal text-slate-600 max-w-[240px] truncate" title="${(lokasi.keterangan_alamat || '').replace(/"/g, '&quot;')}">
                        ${lokasi.keterangan_alamat || '-'}
                    </td>
                    <td class="py-4 px-4 text-center">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-[#0079C6] font-bold text-xs rounded-lg border border-blue-100">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            <span>${lokasi.batas_waktu_henti} Detik</span>
                        </span>
                    </td>
                    <td class="py-4 px-4 text-center">
                        <div class="inline-flex items-center gap-2">
                            <button type="button" 
                                    onclick="openEditLokasiModal(${safeItem})"
                                    class="p-2 text-slate-600 hover:text-[#0079C6] hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                    title="Edit Lokasi">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button type="button" 
                                    onclick="openDeleteLokasiModal('${lokasi.id}', '${safeName}')"
                                    class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                    title="Hapus Lokasi">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    /**
     * Debounced search handler.
     */
    function onSearchInput(val) {
        if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            fetchLokasiData(val.trim());
        }, 300);
    }

    // =========================================================================
    // Modal Form (Create & Edit) via RESTful API
    // =========================================================================

    window.openCreateLokasiModal = function() {
        if (lokasiEditId) lokasiEditId.value = '';
        if (lokasiModalTitle) {
            lokasiModalTitle.innerHTML = `
                <i data-lucide="map-pin" class="w-5 h-5 text-[#0079C6]"></i>
                <span>Tambah Lokasi Baru</span>
            `;
        }
        if (btnSimpanLokasiText) btnSimpanLokasiText.textContent = 'Simpan';
        if (lokasiFormError) lokasiFormError.classList.add('hidden');

        if (inputNamaLokasi) inputNamaLokasi.value = '';
        if (inputUuidLokasi) {
            inputUuidLokasi.value = '';
            inputUuidLokasi.placeholder = 'Masukkan UUID (opsional, kosongkan untuk auto-generate)';
        }
        if (inputApiLokasi) {
            inputApiLokasi.value = '';
            inputApiLokasi.placeholder = 'Masukkan API Key / URL Endpoint Lokasi';
        }
        if (inputKeteranganAlamat) inputKeteranganAlamat.value = '';
        if (inputBatasWaktuHenti) inputBatasWaktuHenti.value = 60;

        if (lokasiModal) {
            lokasiModal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
            if (inputNamaLokasi) inputNamaLokasi.focus();
        }
    };

    window.openEditLokasiModal = function(item) {
        if (lokasiEditId) lokasiEditId.value = item.id;
        if (lokasiModalTitle) {
            lokasiModalTitle.innerHTML = `
                <i data-lucide="edit-3" class="w-5 h-5 text-[#0079C6]"></i>
                <span>Edit Lokasi [ID: ${item.id}]</span>
            `;
        }
        if (btnSimpanLokasiText) btnSimpanLokasiText.textContent = 'Perbarui';
        if (lokasiFormError) lokasiFormError.classList.add('hidden');

        if (inputNamaLokasi) inputNamaLokasi.value = item.nama_lokasi || '';
        if (inputUuidLokasi) {
            inputUuidLokasi.value = '';
            inputUuidLokasi.placeholder = item.uuid_lokasi ? 'Tersimpan sebagai Hash (kosongkan jika tidak diubah)' : 'Masukkan UUID';
        }
        if (inputApiLokasi) {
            inputApiLokasi.value = '';
            inputApiLokasi.placeholder = item.api_lokasi ? 'Tersimpan sebagai Hash (kosongkan jika tidak diubah)' : 'Masukkan API Lokasi';
        }
        if (inputKeteranganAlamat) inputKeteranganAlamat.value = item.keterangan_alamat && item.keterangan_alamat !== '-' ? item.keterangan_alamat : '';
        if (inputBatasWaktuHenti) inputBatasWaktuHenti.value = item.batas_waktu_henti || 60;

        if (lokasiModal) {
            lokasiModal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
            if (inputNamaLokasi) inputNamaLokasi.focus();
        }
    };

    window.closeLokasiModal = function() {
        if (lokasiModal) lokasiModal.classList.add('hidden');
        if (lokasiFormError) lokasiFormError.classList.add('hidden');
    };

    /**
     * Submit Form via POST /api/lokasi or PUT /api/lokasi/{id}.
     */
    window.handleLokasiFormSubmit = async function(e) {
        e.preventDefault();

        const id = lokasiEditId ? lokasiEditId.value : '';
        const isEdit = Boolean(id);

        const payload = {
            nama_lokasi: inputNamaLokasi ? inputNamaLokasi.value.trim() : '',
            keterangan_alamat: inputKeteranganAlamat ? inputKeteranganAlamat.value.trim() : '',
            batas_waktu_henti: inputBatasWaktuHenti ? parseInt(inputBatasWaktuHenti.value) : 60,
        };

        if (inputUuidLokasi && inputUuidLokasi.value.trim()) {
            payload.uuid_lokasi = inputUuidLokasi.value.trim();
        }
        if (inputApiLokasi && inputApiLokasi.value.trim()) {
            payload.api_lokasi = inputApiLokasi.value.trim();
        }

        const url = isEdit ? `/api/lokasi/${id}` : '/api/lokasi';
        const method = isEdit ? 'PUT' : 'POST';

        if (btnSimpanLokasiText) btnSimpanLokasiText.textContent = 'Menyimpan...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify(payload)
            });

            const json = await res.json();

            if (!res.ok || !json.success) {
                const errMsg = json.message || (json.errors ? Object.values(json.errors).flat().join(', ') : 'Terjadi kesalahan validasi.');
                throw new Error(errMsg);
            }

            closeLokasiModal();
            showToast('Berhasil!', json.message || (isEdit ? 'Lokasi berhasil diperbarui!' : 'Lokasi baru berhasil ditambahkan!'));
            
            // Re-fetch data via API
            fetchLokasiData();

        } catch (err) {
            console.error('Submit Lokasi error:', err);
            if (lokasiFormError && lokasiFormErrorText) {
                lokasiFormErrorText.textContent = err.message || 'Gagal menyimpan data lokasi.';
                lokasiFormError.classList.remove('hidden');
            }
        } finally {
            if (btnSimpanLokasiText) {
                btnSimpanLokasiText.textContent = isEdit ? 'Perbarui' : 'Simpan';
            }
        }
    };

    // =========================================================================
    // Delete Confirmation via DELETE /api/lokasi/{id}
    // =========================================================================

    window.openDeleteLokasiModal = function(id, name) {
        deletingLocationId = id;
        if (deleteLokasiNameText) deleteLokasiNameText.textContent = name;
        if (deleteLokasiModal) {
            deleteLokasiModal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    };

    window.closeDeleteLokasiModal = function() {
        deletingLocationId = null;
        if (deleteLokasiModal) deleteLokasiModal.classList.add('hidden');
    };

    window.confirmDeleteLokasi = async function() {
        if (!deletingLocationId) return;

        if (btnConfirmDeleteLokasi) btnConfirmDeleteLokasi.textContent = 'Menghapus...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const res = await fetch(`/api/lokasi/${deletingLocationId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            });

            const json = await res.json();

            if (!res.ok || !json.success) {
                throw new Error(json.message || 'Gagal menghapus lokasi.');
            }

            closeDeleteLokasiModal();
            showToast('Dihapus!', json.message || 'Lokasi berhasil dihapus.');

            // Refresh table from API
            fetchLokasiData();

        } catch (err) {
            console.error('Delete error:', err);
            showToast('Gagal Hapus', err.message || 'Gagal menghapus lokasi dari server.', false);
        } finally {
            if (btnConfirmDeleteLokasi) btnConfirmDeleteLokasi.textContent = 'Hapus';
        }
    };

    // Close on backdrop click
    if (lokasiBackdrop) lokasiBackdrop.addEventListener('click', closeLokasiModal);
    if (deleteLokasiBackdrop) deleteLokasiBackdrop.addEventListener('click', closeDeleteLokasiModal);

    // Initial API Sync
    document.addEventListener('DOMContentLoaded', () => {
        fetchLokasiData();
    });
</script>
@endpush
