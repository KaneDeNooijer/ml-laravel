@extends('layouts.app', ['title' => __('ui.meals.new').' · '.config('app.name')])

@section('content')
    <main class="mx-auto max-w-3xl px-4 py-6 sm:px-6 sm:py-8">
        <h1 class="text-3xl font-bold tracking-tight">{{ __('ui.meals.new') }}</h1>

        <div class="mt-6">
            <x-bladewind::card>
                @include('meals._form', [
                    'action' => route('meals.store'),
                    'method' => 'POST',
                    'submitLabel' => __('ui.meals.create'),
                ])
            </x-bladewind::card>
        </div>
    </main>
@endsection
