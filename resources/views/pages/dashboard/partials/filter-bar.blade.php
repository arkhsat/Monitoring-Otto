<!-- Filter Controls Bar -->
<section class="grid grid-cols-1 md:grid-cols-2 gap-4 relative z-30">
    {{-- Location Filter Card --}}
    @include('pages.dashboard.partials.location-filter')

    {{-- Date Filter Card --}}
    @include('pages.dashboard.partials.date-filter')
</section>
