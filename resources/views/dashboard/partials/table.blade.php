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
                @php
                    $records = $records ?? [
                        ['date' => 'Kamis, 1 Oktober 2026', 'admin' => '900.000', 'mdd' => '960.000', 'report' => '900.000', 'status' => 'Tidak Cocok'],
                        ['date' => 'Jumat, 2 Oktober 2026', 'admin' => '900.000', 'mdd' => '900.000', 'report' => '900.000', 'status' => 'Cocok'],
                        ['date' => 'Sabtu, 3 Oktober 2026', 'admin' => '900.000', 'mdd' => '900.000', 'report' => '900.000', 'status' => 'Cocok'],
                        ['date' => 'Minggu, 4 Oktober 2026', 'admin' => '900.000', 'mdd' => '960.000', 'report' => '900.000', 'status' => 'Tidak Cocok'],
                        ['date' => 'Senin, 5 Oktober 2026', 'admin' => '900.000', 'mdd' => '900.000', 'report' => '900.000', 'status' => 'Cocok'],
                        ['date' => 'Selasa, 6 Oktober 2026', 'admin' => '900.000', 'mdd' => '900.000', 'report' => '900.000', 'status' => 'Cocok'],
                        ['date' => 'Rabu, 7 Oktober 2026', 'admin' => '900.000', 'mdd' => '960.000', 'report' => '900.000', 'status' => 'Tidak Cocok'],
                        ['date' => 'Kamis, 8 Oktober 2026', 'admin' => '900.000', 'mdd' => '900.000', 'report' => '900.000', 'status' => 'Cocok'],
                        ['date' => 'Jumat, 9 Oktober 2026', 'admin' => '920.000', 'mdd' => '920.000', 'report' => '920.000', 'status' => 'Cocok'],
                        ['date' => 'Sabtu, 10 Oktober 2026', 'admin' => '950.000', 'mdd' => '980.000', 'report' => '950.000', 'status' => 'Tidak Cocok'],
                        ['date' => 'Minggu, 11 Oktober 2026', 'admin' => '950.000', 'mdd' => '950.000', 'report' => '950.000', 'status' => 'Cocok'],
                        ['date' => 'Senin, 12 Oktober 2026', 'admin' => '910.000', 'mdd' => '910.000', 'report' => '910.000', 'status' => 'Cocok'],
                    ];
                @endphp

                @foreach ($records as $row)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-4 px-4 font-normal">{{ $row['date'] }}</td>
                        <td class="py-4 px-4 font-normal">{{ $row['admin'] }}</td>
                        <td class="py-4 px-4 font-normal">{{ $row['mdd'] }}</td>
                        <td class="py-4 px-4 font-normal">{{ $row['report'] }}</td>
                        <td class="py-4 px-4 font-bold {{ $row['status'] === 'Tidak Cocok' ? 'text-[#FF0000]' : 'text-slate-900' }}">
                            {{ $row['status'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>