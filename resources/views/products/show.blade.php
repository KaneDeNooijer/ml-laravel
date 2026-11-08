@extends('layouts.app', ['title' => ($product['name'] ?? __('ui.products.title')).' · '.config('app.name')])

@section('content')
    <main class="mx-auto flex max-w-2xl flex-col gap-5 px-4 py-6 sm:px-6 sm:py-8">
        @if ($error !== null)
            <x-bladewind::alert type="error" :show_close_icon="false">{{ $error }}</x-bladewind::alert>
        @else
            <div>
                <p class="text-sm text-slate-500">{{ __('ui.products.barcode', ['barcode' => $product['barcode']]) }}</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight">{{ $product['name'] !== '' ? $product['name'] : __('ui.products.unnamed') }}</h1>
                @if ($product['brands'] !== '')
                    <p class="mt-1 text-slate-600">{{ $product['brands'] }}</p>
                @endif
            </div>

            <x-bladewind::card>
                <div class="flex flex-col gap-4" data-nova>
                    <div>
                        <h2 class="text-xl font-bold">{{ __('ui.nova.title') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('ui.nova.intro') }}</p>
                    </div>

                    @if ($product['nova']['group'] === null)
                        <p class="text-slate-600">{{ __('ui.nova.unknown') }}</p>
                    @else
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-2xl font-bold">{{ __('ui.nova.group', ['group' => $product['nova']['group']]) }}</span>
                            <span class="text-slate-700">{{ __('ui.nova.groups.'.$product['nova']['group']) }}</span>
                            @if ($product['nova']['estimated'])
                                <x-bladewind::tag :label="__('ui.nova.estimated')" color="yellow" :uppercasing="false" />
                            @endif
                        </div>

                        @if ($product['nova']['estimated'])
                            <p class="text-sm text-slate-600">{{ __('ui.nova.estimate_explanation', ['model' => $product['nova']['model'], 'count' => $product['nova']['trained_on'], 'category' => $product['nova']['category']]) }}</p>
                            @if ($product['nova']['confidence'] !== null)
                                <p class="text-sm font-medium">{{ __('ui.nova.confidence', ['percent' => (int) round($product['nova']['confidence'] * 100)]) }}</p>
                            @endif
                            @if ($product['nova']['reasons'] !== [])
                                <div>
                                    <p class="text-sm font-medium">{{ __('ui.nova.reasons') }}</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($product['nova']['reasons'] as $reason)
                                            <x-bladewind::tag :label="$reason" color="gray" :uppercasing="false" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-sm text-slate-600">{{ __('ui.nova.measured') }}</p>
                        @endif
                    @endif
                </div>
            </x-bladewind::card>
        @endif

        <x-bladewind::button tag="a" href="{{ route('scanner.index') }}" type="secondary" icon="qr-code" radius="medium" uppercasing="false">{{ __('ui.products.scan_again') }}</x-bladewind::button>
    </main>
@endsection
