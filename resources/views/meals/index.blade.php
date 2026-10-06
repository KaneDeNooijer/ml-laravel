@extends('layouts.app', ['title' => __('ui.meals.title').' · '.config('app.name')])

@section('content')
    <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-3xl font-bold tracking-tight">{{ __('ui.meals.title') }}</h1>
            <x-bladewind::button class="w-full sm:w-auto" tag="a" href="{{ route('meals.create') }}" type="secondary" icon="plus" radius="medium" uppercasing="false">{{ __('ui.meals.new') }}</x-bladewind::button>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-5">
            @forelse ($meals as $meal)
                <x-bladewind::card>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.meals.prepare_on', ['day' => $meal->prep_day->label()]) }}</p>
                        <h2 class="mt-1 text-xl font-bold">{{ $meal->name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('ui.meals.summary', ['servings' => $meal->servings, 'ingredients' => count($meal->ingredients ?? [])]) }}</p>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($meal->ingredients ?? [] as $ingredient)
                            <x-bladewind::tag label="{{ $ingredient['label'] }} · {{ $ingredient['quantity'] }} {{ $ingredient['unit'] }}" color="gray" :uppercasing="false" />
                        @endforeach
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-3 border-t border-slate-200 pt-5 sm:flex sm:flex-wrap">
                        <x-bladewind::button class="w-full sm:w-auto" tag="a" href="{{ route('meals.edit', $meal) }}" type="secondary" icon="pencil-square" size="small" radius="medium" uppercasing="false">{{ __('ui.common.edit') }}</x-bladewind::button>
                        <form action="{{ route('meals.destroy', $meal) }}" method="POST" class="w-full sm:w-auto" onsubmit="return confirm({{ Js::from(__('ui.meals.delete_confirm')) }})">
                            @csrf
                            @method('DELETE')
                            <x-bladewind::button class="w-full" can_submit="true" color="red" outline="true" icon="trash" size="small" radius="medium" uppercasing="false">{{ __('ui.common.delete') }}</x-bladewind::button>
                        </form>
                    </div>
                </x-bladewind::card>
            @empty
                <div class="lg:col-span-2">
                    <x-bladewind::empty-state heading="{{ __('ui.meals.empty') }}" :show_image="false">
                        <x-bladewind::button tag="a" href="{{ route('meals.create') }}" type="secondary" icon="plus" size="small" radius="medium" uppercasing="false">{{ __('ui.meals.empty_action') }}</x-bladewind::button>
                    </x-bladewind::empty-state>
                </div>
            @endforelse
        </div>
    </main>
@endsection
