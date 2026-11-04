@foreach ($locations ?? [] as $loc)
    <div class="location-item p-3 cursor-pointer {{ ($loc['selected'] ?? false) ? 'bg-[#BBE4FF]/50 hover:bg-[#BBE4FF]' : 'hover:bg-slate-50' }} transition-colors"
         data-id="{{ $loc['id'] ?? $loc['kode'] ?? $loop->iteration }}"
         data-kode="{{ $loc['kode'] ?? $loc['id'] ?? $loop->iteration }}"
         data-name="{{ $loc['name'] }}" 
         data-address="{{ $loc['address'] }}">
        <div class="font-bold text-black text-sm">{{ $loc['name'] }}</div>
        <div class="text-[11px] text-slate-500 leading-tight mt-0.5">
            {{ $loc['address'] }}
        </div>
    </div>
@endforeach
