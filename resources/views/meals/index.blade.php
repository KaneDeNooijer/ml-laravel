@extends('layouts.app', ['title' => 'LiveList · Maaltijden'])

@section('content')
    <main class="mx-auto max-w-6xl px-5 py-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-brand-700">Weekmenu voorbereiden</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight">Maaltijden</h1>
                <p class="mt-2 max-w-2xl text-slate-600">Maak zelf maaltijden en leg per maaltijd vast welke algemene ingrediënten je tijdens het winkelen wilt invullen.</p>
            </div>
            <a href="{{ route('meals.create') }}" class="rounded-xl bg-brand-700 px-5 py-3 text-center font-semibold text-white shadow-sm hover:bg-brand-900">Nieuwe maaltijd</a>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-2">
            @forelse ($meals as $meal)
                <x-bladewind::card>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-brand-700">{{ $meal->prep_day }} prep</p>
                            <h2 class="mt-1 text-xl font-bold">{{ $meal->name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $meal->servings }} porties · {{ count($meal->ingredients ?? []) }} ingrediënten · {{ count($meal->selections ?? []) }} gekozen</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ count($meal->selections ?? []) }}/{{ count($meal->ingredients ?? []) }}</span>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($meal->ingredients ?? [] as $ingredient)
                            <span class="rounded-lg bg-brand-50 px-3 py-2 text-sm text-brand-900">{{ $ingredient['label'] }} · {{ $ingredient['quantity'] }} {{ $ingredient['unit'] }}</span>
                        @endforeach
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                        <a href="{{ route('demo', ['meal' => (string) $meal->id]) }}" class="rounded-xl bg-brand-700 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Open scanner</a>
                        <a href="{{ route('meals.edit', $meal) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Bewerken</a>
                        <form action="{{ route('meals.destroy', $meal) }}" method="POST" onsubmit="return confirm('Deze maaltijd en alle scans verwijderen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Verwijderen</button>
                        </form>
                    </div>
                </x-bladewind::card>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center md:col-span-2">
                    <h2 class="text-xl font-bold">Nog geen maaltijden</h2>
                    <p class="mt-2 text-slate-500">Maak je eerste maaltijd om de boodschappenflow te starten.</p>
                </div>
            @endforelse
        </div>
    </main>
@endsection
