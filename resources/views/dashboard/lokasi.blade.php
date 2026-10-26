<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Lokasi - System Monitoring Otto</title>

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
            @include('dashboard.partials.header', ['headerTitle' => 'Lokasi'])

            <!-- Flash Success Notification -->
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            <!-- Main Table Section for Location Management -->
            <section class="bg-white rounded-xl shadow-lg p-4 md:p-6 flex-1 flex flex-col min-h-[500px]">
                
                <!-- Action Bar: Title & "Tambah Lokasi" Button -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-lg md:text-xl font-bold text-black tracking-tight">Daftar Lokasi Onsite</h2>
                        <p class="text-xs md:text-sm text-slate-500">Kelola dan daftarkan lokasi onsite parkir beserta ambang batas waktu henti</p>
                    </div>

                    <!-- Button Tambah Lokasi (Matches Figma) -->
                    <button type="button" 
                            onclick="openCreateLokasiModal()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#0079C6] hover:bg-[#005f9e] active:scale-95 text-white font-bold text-sm md:text-base rounded-xl shadow-md transition-all cursor-pointer">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        <span>Tambah Lokasi</span>
                    </button>
                </div>

                <!-- Scrollable Table Container -->
                <div class="overflow-y-auto overflow-x-auto figma-scrollbar max-h-[600px] pr-2">
                    <table class="w-full text-center border-collapse">
                        <!-- Sticky Table Head -->
                        <thead class="sticky top-0 bg-white z-10">
                            <tr class="border-b-2 border-slate-900 text-black font-bold text-base md:text-lg">
                                <th class="py-3 px-4 text-left">Nama Lokasi</th>
                                <th class="py-3 px-4 text-left">Keterangan Alamat</th>
                                <th class="py-3 px-4 text-center">Batas Waktu Henti</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody id="lokasiTableBody" class="divide-y divide-slate-100 text-slate-800 text-sm md:text-base">
                            @forelse ($lokasis ?? [] as $lokasi)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <!-- Nama Lokasi -->
                                    <td class="py-4 px-4 text-left font-bold text-slate-900">
                                        {{ $lokasi->nama_lokasi }}
                                    </td>

                                    <!-- Keterangan Alamat -->
                                    <td class="py-4 px-4 text-left font-normal text-slate-600 max-w-[360px]">
                                        {{ $lokasi->keterangan_alamat ?: '-' }}
                                    </td>

                                    <!-- Batas Waktu Henti (Downtime Threshold) -->
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-[#0079C6] font-bold text-sm rounded-lg border border-blue-100">
                                            <i data-lucide="clock" class="w-4 h-4"></i>
                                            <span>{{ $lokasi->batas_waktu_henti }} Detik</span>
                                        </span>
                                    </td>

                                    <!-- Aksi (Edit & Delete) -->
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-2">
                                            <!-- Edit Button -->
                                            <button type="button" 
                                                    onclick='openEditLokasiModal(@json($lokasi))'
                                                    class="p-2 text-slate-600 hover:text-[#0079C6] hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Edit Lokasi">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </button>

                                            <!-- Delete Button -->
                                            <button type="button" 
                                                    onclick="openDeleteLokasiModal('{{ $lokasi->id }}', '{{ addslashes($lokasi->nama_lokasi) }}')"
                                                    class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                    title="Hapus Lokasi">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-400 font-medium">
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

        </main>
    </div>

    {{-- Lokasi Modal Form & Delete Confirmation --}}
    @include('dashboard.partials.lokasi-modal')

    <!-- Interactive JavaScript -->
    <script>
        // 1. Initialize Lucide Icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // 2. Sidebar Shrink / Expand Logic
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

        // 3. Lokasi Modal Logic
        const lokasiModal = document.getElementById('lokasiModal');
        const lokasiBackdrop = document.getElementById('lokasiBackdrop');
        const lokasiModalTitle = document.getElementById('lokasiModalTitle');
        const lokasiForm = document.getElementById('lokasiForm');
        const lokasiFormMethod = document.getElementById('lokasiFormMethod');
        const inputNamaLokasi = document.getElementById('nama_lokasi');
        const inputKeteranganAlamat = document.getElementById('keterangan_alamat');
        const inputBatasWaktuHenti = document.getElementById('batas_waktu_henti');

        const deleteLokasiModal = document.getElementById('deleteLokasiModal');
        const deleteLokasiBackdrop = document.getElementById('deleteLokasiBackdrop');
        const deleteLokasiForm = document.getElementById('deleteLokasiForm');
        const deleteLokasiNameText = document.getElementById('deleteLokasiNameText');

        window.openCreateLokasiModal = function() {
            lokasiModalTitle.innerHTML = `
                <i data-lucide="map-pin" class="w-5 h-5 text-[#0079C6]"></i>
                <span>Tambah Lokasi Baru</span>
            `;
            lokasiForm.action = "{{ route('dashboard.lokasi.store') }}";
            lokasiFormMethod.value = "POST";
            inputNamaLokasi.value = '';
            inputKeteranganAlamat.value = '';
            inputBatasWaktuHenti.value = 60;

            lokasiModal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
            inputNamaLokasi.focus();
        };

        window.openEditLokasiModal = function(item) {
            lokasiModalTitle.innerHTML = `
                <i data-lucide="edit-3" class="w-5 h-5 text-[#0079C6]"></i>
                <span>Edit Lokasi</span>
            `;
            lokasiForm.action = `/lokasi/${item.id}`;
            lokasiFormMethod.value = "PUT";
            inputNamaLokasi.value = item.nama_lokasi || '';
            inputKeteranganAlamat.value = item.keterangan_alamat || '';
            inputBatasWaktuHenti.value = item.batas_waktu_henti || 60;

            lokasiModal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
            inputNamaLokasi.focus();
        };

        window.closeLokasiModal = function() {
            if (lokasiModal) lokasiModal.classList.add('hidden');
        };

        window.openDeleteLokasiModal = function(id, name) {
            deleteLokasiNameText.textContent = name;
            deleteLokasiForm.action = `/lokasi/${id}`;
            deleteLokasiModal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        };

        window.closeDeleteLokasiModal = function() {
            if (deleteLokasiModal) deleteLokasiModal.classList.add('hidden');
        };

        // Dark background click to close
        if (lokasiBackdrop) {
            lokasiBackdrop.addEventListener('click', closeLokasiModal);
        }
        if (deleteLokasiBackdrop) {
            deleteLokasiBackdrop.addEventListener('click', closeDeleteLokasiModal);
        }
    </script>
</body>
</html>

