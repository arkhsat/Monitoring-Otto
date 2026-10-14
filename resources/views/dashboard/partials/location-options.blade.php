@php
    $locations = $locations ?? [
        [
            'name' => 'True Blue',
            'address' => 'Jl. Raden Saleh Raya No.39, RT.1/RW.4, Cikini, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10330',
            'selected' => true,
        ],
        [
            'name' => 'Gelael Tebet',
            'address' => 'Jl. Raden Saleh Raya No.39, RT.1/RW.4, Cikini, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10330',
            'selected' => false,
        ],
        [
            'name' => 'Intermark BSD',
            'address' => 'Jl. Raden Saleh Raya No.39, RT.1/RW.4, Cikini, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10330',
            'selected' => false,
        ],
        [
            'name' => 'Ciracas Center',
            'address' => 'Jl. Raden Saleh Raya No.39, RT.1/RW.4, Cikini, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10330',
            'selected' => false,
        ],
    ];
@endphp

@foreach ($locations as $loc)
    <div class="location-item p-3 cursor-pointer {{ ($loc['selected'] ?? false) ? 'bg-[#BBE4FF]/50 hover:bg-[#BBE4FF]' : 'hover:bg-slate-50' }} transition-colors"
         data-name="{{ $loc['name'] }}" 
         data-address="{{ $loc['address'] }}">
        <div class="font-bold text-black text-sm">{{ $loc['name'] }}</div>
        <div class="text-[11px] text-slate-500 leading-tight mt-0.5">
            {{ $loc['address'] }}
        </div>
    </div>
@endforeach
