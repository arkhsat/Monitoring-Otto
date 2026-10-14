<!-- Table Card with Explicit Scroll Container & Styled Scrollbar -->
<section class="bg-white rounded-xl shadow-lg p-4 md:p-6 flex-1 flex flex-col min-h-[500px]">
    
    <!-- Scrollable Table Container -->
    <div class="overflow-y-auto overflow-x-auto figma-scrollbar max-h-[600px] pr-2">
        <table class="w-full text-center border-collapse">
            <!-- Sticky Table Head -->
            <thead class="sticky top-0 bg-white z-10">
                <tr class="border-b-2 border-slate-900 text-black font-bold text-base md:text-lg">
                    <th class="py-3 px-4 text-center">Tanggal</th>
                    <th class="py-3 px-4 text-center">Admin Otto</th>
                    <th class="py-3 px-4 text-center">Settlement MDD</th>
                    <th class="py-3 px-4 text-center">Report Lokasi</th>
                    <th class="py-3 px-4 text-center">Kode</th>
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody id="tableBody" class="divide-y divide-slate-100 text-slate-800 text-sm md:text-base">
                @forelse ($records ?? [] as $row)
                    @php
                        $date = $row['date'] ?? '-';
                        $admin = $row['admin'] ?? '-';
                        $mdd = $row['mdd'] ?? '-';
                        $report = $row['report'] ?? '-';
                        $status = $row['status'] ?? 'Cocok';
                        $isNotMatch = ($status === 'Tidak Cocok');
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-4 px-4 font-normal">{{ $date }}</td>
                        <td class="py-4 px-4 font-normal">{{ $admin }}</td>
                        <td class="py-4 px-4 font-normal">{{ $mdd }}</td>
                        <td class="py-4 px-4 font-normal">{{ $report }}</td>
                        <td class="py-4 px-4 font-bold {{ $isNotMatch ? 'text-[#FF0000]' : 'text-slate-900' }}">
                            {{ $status }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 font-medium">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                <span>Tidak ada data transaksi untuk lokasi ini.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>