<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <title>{{ $title ?? config('app.name') }}</title>
        <link rel="stylesheet" href="{{ asset('vendor/bladewind/css/bladewind-ui-no-preflight.min.css') }}">
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
    </head>
    <body class="pb-[calc(4rem+env(safe-area-inset-bottom))] lg:pb-0">
        @bladewindScripts

        <header class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)] lg:static lg:h-16 lg:border-t-0 lg:border-b lg:pb-0">
            <div class="mx-auto h-16 max-w-md lg:flex lg:max-w-6xl lg:items-center lg:justify-between lg:px-6">
                <a href="{{ route('home.index') }}" class="hidden text-xl font-bold tracking-tight text-slate-900 lg:block">{{ config('app.name') }}</a>
                <nav class="grid h-full w-full grid-cols-4 lg:flex lg:w-auto lg:items-center lg:gap-1" aria-label="{{ __('ui.nav.label') }}">
                    <a
                        href="{{ route('home.index') }}"
                        @if (request()->routeIs('home.*')) aria-current="page" @endif
                        @class([
                            'relative flex h-full flex-col items-center justify-center gap-1 text-xs font-medium transition-colors after:absolute after:inset-x-3 after:bottom-0 after:hidden after:h-0.5 after:rounded-full lg:flex-row lg:gap-2 lg:px-3 lg:text-sm lg:after:block',
                            'text-slate-900 lg:after:bg-slate-900' => request()->routeIs('home.*'),
                            'text-slate-500 hover:text-slate-700 lg:after:bg-transparent lg:hover:text-slate-900' => ! request()->routeIs('home.*'),
                        ])
                    >
                        <x-bladewind::icon name="home" :type="request()->routeIs('home.*') ? 'solid' : 'outline'" size="medium" />
                        <span>{{ __('ui.nav.home') }}</span>
                    </a>
                    <a
                        href="{{ route('scanner.index') }}"
                        @if (request()->routeIs('scanner.*')) aria-current="page" @endif
                        @class([
                            'relative flex h-full flex-col items-center justify-center gap-1 text-xs font-medium transition-colors after:absolute after:inset-x-3 after:bottom-0 after:hidden after:h-0.5 after:rounded-full lg:flex-row lg:gap-2 lg:px-3 lg:text-sm lg:after:block',
                            'text-slate-900 lg:after:bg-slate-900' => request()->routeIs('scanner.*'),
                            'text-slate-500 hover:text-slate-700 lg:after:bg-transparent lg:hover:text-slate-900' => ! request()->routeIs('scanner.*'),
                        ])
                    >
                        <x-bladewind::icon name="qr-code" :type="request()->routeIs('scanner.*') ? 'solid' : 'outline'" size="medium" />
                        <span>{{ __('ui.nav.scanner') }}</span>
                    </a>
                    <a
                        href="{{ route('meals.index') }}"
                        @if (request()->routeIs('meals.*')) aria-current="page" @endif
                        @class([
                            'relative flex h-full flex-col items-center justify-center gap-1 text-xs font-medium transition-colors after:absolute after:inset-x-3 after:bottom-0 after:hidden after:h-0.5 after:rounded-full lg:flex-row lg:gap-2 lg:px-3 lg:text-sm lg:after:block',
                            'text-slate-900 lg:after:bg-slate-900' => request()->routeIs('meals.*'),
                            'text-slate-500 hover:text-slate-700 lg:after:bg-transparent lg:hover:text-slate-900' => ! request()->routeIs('meals.*'),
                        ])
                    >
                        <x-bladewind::icon name="clipboard-document-list" :type="request()->routeIs('meals.*') ? 'solid' : 'outline'" size="medium" />
                        <span>{{ __('ui.nav.meals') }}</span>
                    </a>
                    <span class="mx-2 hidden h-6 w-px bg-slate-200 lg:block" aria-hidden="true"></span>
                    <a
                        href="{{ route('account.index') }}"
                        @if (request()->routeIs('account.*')) aria-current="page" @endif
                        @class([
                            'relative flex h-full flex-col items-center justify-center gap-1 text-xs font-medium transition-colors after:absolute after:inset-x-3 after:bottom-0 after:hidden after:h-0.5 after:rounded-full lg:flex-row lg:gap-2 lg:px-3 lg:text-sm lg:after:block',
                            'text-slate-900 lg:after:bg-slate-900' => request()->routeIs('account.*'),
                            'text-slate-500 hover:text-slate-700 lg:after:bg-transparent lg:hover:text-slate-900' => ! request()->routeIs('account.*'),
                        ])
                    >
                        <x-bladewind::icon name="user-circle" :type="request()->routeIs('account.*') ? 'solid' : 'outline'" size="medium" />
                        <span>{{ __('ui.nav.account') }}</span>
                    </a>
                </nav>
            </div>
        </header>

        @if (session('status'))
            <div class="mx-auto max-w-6xl px-6 pt-6">
                <x-bladewind::alert type="success" :show_close_icon="false">{{ session('status') }}</x-bladewind::alert>
            </div>
        @endif

        @yield('content')
    </body>
</html>
