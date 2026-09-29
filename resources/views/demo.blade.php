@extends('layouts.app', ['title' => 'LiveList · Productkeuze'])

@section('content')
        <main class="mx-auto grid max-w-6xl gap-6 px-5 py-8 lg:grid-cols-[minmax(0,1.25fr)_minmax(300px,.75fr)]">
            <section class="space-y-6">
                <x-bladewind::card>
                    <p class="text-sm font-semibold uppercase tracking-wider text-brand-700">{{ $demo['prep_day'] }} prep</p>
                    <h1 class="mt-1 text-3xl font-black tracking-tight">{{ $demo['name'] }}</h1>
                    <p class="mt-2 text-slate-600">{{ $demo['servings'] }} porties · kies een ingrediënt en scan het product dat voor je staat.</p>

                    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($demo['ingredients'] as $ingredient)
                            <label class="cursor-pointer rounded-2xl border border-slate-200 bg-white p-4 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 has-[:checked]:ring-2 has-[:checked]:ring-brand-100">
                                <input class="sr-only" form="scan-form" type="radio" name="category" value="{{ $ingredient['category'] }}" @checked($loop->first)>
                                <span class="block font-bold">{{ $ingredient['label'] }}</span>
                                <span class="mt-1 block text-sm text-slate-500">{{ $ingredient['quantity'] }} {{ $ingredient['unit'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </x-bladewind::card>

                <x-bladewind::card>
                    <h2 class="text-xl font-bold">Scan een product</h2>
                    <p class="mt-1 text-sm text-slate-500">De productdata wordt live via de Laravel-backend bij Open Food Facts opgehaald.</p>

                    <form id="scan-form" data-scan-form action="{{ route('scans.store') }}" class="mt-5 space-y-4">
                        <input type="hidden" name="meal_id" value="{{ $demo['meal_id'] }}">
                        <div>
                            <label for="barcode" class="mb-2 block text-sm font-semibold">Barcode</label>
                            <input id="barcode" name="barcode" inputmode="numeric" autocomplete="off" placeholder="Bijvoorbeeld 8712566328932" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button type="submit" class="rounded-xl bg-brand-700 px-5 py-3 font-semibold text-white shadow-sm hover:bg-brand-900 disabled:opacity-50">Product controleren</button>
                            <button type="button" data-start-camera class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50">Camera openen</button>
                        </div>
                    </form>

                    <div data-camera-panel hidden class="mt-5 overflow-hidden rounded-2xl bg-slate-950 p-3">
                        <video data-camera-video class="aspect-video w-full rounded-xl object-cover"></video>
                        <button type="button" data-stop-camera class="mt-3 w-full rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white">Camera stoppen</button>
                    </div>

                    <div data-message hidden class="mt-5"></div>
                </x-bladewind::card>

                <x-bladewind::card data-result hidden></x-bladewind::card>
            </section>

            <aside class="space-y-6">
                <x-bladewind::card>
                    <h2 class="text-lg font-bold">Maaltijdstatus</h2>
                    <div data-meal-state class="mt-5 grid grid-cols-2 gap-5">
                        <div><span class="text-sm text-slate-500">Prep-score</span><strong class="block text-3xl text-brand-700">{{ $demo['prep_score']['score'] }}/100</strong></div>
                        <div><span class="text-sm text-slate-500">Datavolledigheid</span><strong class="block text-xl">{{ $demo['prep_score']['completeness'] }}%</strong></div>
                        <div><span class="text-sm text-slate-500">Energie / portie</span><strong class="block text-xl">{{ number_format($demo['nutrition']['per_portion']['energy_kcal'], 1, ',', '.') }} kcal</strong></div>
                        <div><span class="text-sm text-slate-500">Eiwit / portie</span><strong class="block text-xl">{{ number_format($demo['nutrition']['per_portion']['protein'], 1, ',', '.') }} g</strong></div>
                    </div>
                    <p class="mt-5 rounded-xl bg-amber-50 p-3 text-xs leading-relaxed text-amber-900">De prep-score is een transparante demoheuristiek, geen persoonlijk voedings- of medisch advies.</p>
                </x-bladewind::card>

                <x-bladewind::card>
                    <h2 class="text-lg font-bold">Wat gebeurt er?</h2>
                    <ol class="mt-4 space-y-4 text-sm text-slate-600">
                        <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-100 font-bold text-brand-900">1</span><span>Laravel zoekt de barcode live op.</span></li>
                        <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-100 font-bold text-brand-900">2</span><span>De baseline en het Python-model classificeren het product.</span></li>
                        <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-100 font-bold text-brand-900">3</span><span>Voedingswaarden worden omgerekend naar hoeveelheid en portie.</span></li>
                        <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-100 font-bold text-brand-900">4</span><span>Jij bepaalt of het product wordt gebruikt.</span></li>
                    </ol>
                </x-bladewind::card>
            </aside>
        </main>

@endsection
