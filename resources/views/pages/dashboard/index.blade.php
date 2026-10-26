@extends('layouts.app', [
    'title' => 'Dashboard System Monitoring Otto',
    'headerTitle' => 'Main'
])

@section('content')
    {{-- Filter Controls Bar (Location & Date) --}}
    @include('pages.dashboard.partials.filter-bar')

    {{-- Data Table Card --}}
    @include('pages.dashboard.partials.table')
@endsection

@section('modals')
    {{-- Settlement & Confirmation Modals --}}
    @include('pages.dashboard.partials.settlement-modal')
@endsection

@push('scripts')
    {{-- Page-Specific Interactive JavaScript --}}
    @include('pages.dashboard.partials.scripts')
@endpush
