<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0f766e">
        <title>{{ $title ?? 'LiveList' }}</title>
        <link rel="stylesheet" href="{{ asset('vendor/bladewind/css/bladewind-ui-no-preflight.min.css') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('demo') }}">
                    <p class="text-xl font-black tracking-tight text-brand-900">LiveList</p>
                    <p class="text-xs text-slate-500">Slim kiezen tijdens het boodschappen doen</p>
                </a>
                <nav class="flex items-center gap-2 text-sm font-semibold">
                    <a href="{{ route('demo') }}" @class(['rounded-xl px-4 py-2', 'bg-brand-100 text-brand-900' => request()->routeIs('demo'), 'text-slate-600 hover:bg-slate-100' => ! request()->routeIs('demo')])>Scannen</a>
                    <a href="{{ route('meals.index') }}" @class(['rounded-xl px-4 py-2', 'bg-brand-100 text-brand-900' => request()->routeIs('meals.*'), 'text-slate-600 hover:bg-slate-100' => ! request()->routeIs('meals.*')])>Maaltijden</a>
                    <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600 sm:inline">Technische POC</span>
                </nav>
            </div>
        </header>

        @if (session('status'))
            <div class="mx-auto max-w-6xl px-5 pt-6">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
            </div>
        @endif

        @yield('content')

        <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    </body>
</html>
