@extends('layouts.app', ['title' => 'LiveList · Maaltijd bewerken'])

@section('content')
    <main class="mx-auto max-w-3xl px-5 py-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-brand-700">Weekmenu voorbereiden</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight">Maaltijd bewerken</h1>
        <p class="mt-2 text-slate-600">Bij opslaan worden bestaande productkeuzes gewist, zodat hoeveelheden en voedingswaarden kloppen.</p>

        <div class="mt-7">
            <x-bladewind::card>
                @include('meals._form', [
                    'action' => route('meals.update', $meal),
                    'method' => 'PUT',
                    'submitLabel' => 'Wijzigingen opslaan',
                ])
            </x-bladewind::card>
        </div>
    </main>
@endsection
