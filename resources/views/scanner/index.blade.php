@extends('layouts.app', ['title' => __('ui.scanner.title').' · '.config('app.name')])

@section('content')
    <x-drawer
        data-scanner-page
        data-product-url="{{ route('products.show', ['product' => '00000000']) }}"
        content-id="scan-drawer-content"
        initial-state="split"
        :label="__('ui.scanner.drawer_label')"
        :handle-label="__('ui.scanner.drawer_handle')"
        class="h-[calc(100dvh-4rem-env(safe-area-inset-bottom))] bg-slate-950 lg:h-[calc(100vh-4rem)] lg:min-h-[32rem]"
    >
        <x-slot:background>
            <x-barcode-scanner class="h-full w-full" />
        </x-slot:background>
    </x-drawer>
@endsection

@once
    @vite('resources/js/scanner-page.ts')
@endonce
