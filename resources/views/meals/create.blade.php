@extends('layouts.app', ['title' => 'LiveList · Nieuwe maaltijd'])

@section('content')
    <main class="mx-auto max-w-3xl px-5 py-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-brand-700">Weekmenu voorbereiden</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight">Nieuwe maaltijd</h1>
        <p class="mt-2 text-slate-600">Voer de maaltijd en benodigde ingrediëntcategorieën zelf in.</p>

        <div class="mt-7">
            <x-bladewind::card>
                @include('meals._form', [
                    'action' => route('meals.store'),
                    'method' => 'POST',
                    'submitLabel' => 'Maaltijd aanmaken',
                ])
            </x-bladewind::card>
        </div>
    </main>
@endsection
