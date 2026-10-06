<div
    {{ $attributes->class(['group/scanner relative overflow-hidden bg-slate-950']) }}
    data-barcode-scanner
    data-state="starting"
>
    <video
        data-camera-video
        class="h-full w-full object-cover opacity-0 transition-opacity duration-500 ease-out group-data-[state=disabled]/scanner:opacity-100 group-data-[state=searching]/scanner:opacity-100 motion-reduce:transition-none"
        playsinline
        muted
    ></video>
    <canvas data-camera-frame hidden class="h-full w-full object-cover"></canvas>

    <svg data-scan-overlay class="pointer-events-none absolute inset-0 h-full w-full" aria-hidden="true">
        <defs>
            <mask id="barcode-scanner-surround-mask" maskUnits="userSpaceOnUse">
                <rect data-scan-mask-background fill="white" />
                <rect data-scan-mask-cutout rx="12" fill="black" />
            </mask>
        </defs>
        <g mask="url(#barcode-scanner-surround-mask)">
            <rect data-scan-overlay-background fill="rgb(0 0 0 / 0.3)" />
        </g>
    </svg>

    <div
        data-barcode-success-ring
        class="pointer-events-none absolute rounded-xl border-2 border-emerald-500 opacity-0 will-change-transform"
    ></div>

    <div data-barcode-outline hidden class="pointer-events-none absolute">
        <div
            data-barcode-outline-frame
            class="absolute inset-0 rounded-xl border-2 border-white/90 bg-transparent group-data-[state=complete]/scanner:border-emerald-500 group-data-[state=complete]/scanner:bg-emerald-500/25 group-data-[state=success]/scanner:border-emerald-500 group-data-[state=success]/scanner:bg-emerald-500/25"
        ></div>
    </div>

    <div
        data-barcode-message
        data-default-message="{{ __('ui.scanner.scan_prompt') }}"
        data-success-message="{{ __('ui.scanner.scan_success') }}"
        hidden
        class="pointer-events-none absolute z-10 grid w-max max-w-[calc(100%-2rem)] -translate-x-1/2 text-xs font-semibold leading-4 text-white text-shadow-sm text-shadow-black transition-[color,opacity] duration-300 group-data-[state=complete]/scanner:text-emerald-400 group-data-[state=detected]/scanner:opacity-0 group-data-[state=success]/scanner:text-emerald-400 motion-reduce:transition-none"
        role="status"
        aria-live="polite"
    >
        <span aria-hidden="true" class="col-start-1 row-start-1 flex items-center justify-center gap-1.5 transition-opacity duration-300 group-data-[state=complete]/scanner:opacity-0 group-data-[state=detected]/scanner:opacity-0 group-data-[state=success]/scanner:opacity-0 motion-reduce:transition-none">
            <span class="flex size-4 shrink-0 items-center justify-center drop-shadow-[0_1px_1px_rgb(0_0_0_/_0.9)] transition-transform duration-300 ease-out group-data-[state=complete]/scanner:scale-0 group-data-[state=detected]/scanner:scale-0 group-data-[state=success]/scanner:scale-0 motion-reduce:transition-none">
                <x-bladewind::icon name="viewfinder-circle" size="small" />
            </span>
            <span>{{ __('ui.scanner.scan_prompt') }}</span>
        </span>
        <span aria-hidden="true" class="col-start-1 row-start-1 flex items-center justify-center gap-1.5 opacity-0 transition-opacity duration-300 group-data-[state=complete]/scanner:opacity-100 group-data-[state=detected]/scanner:opacity-100 group-data-[state=success]/scanner:opacity-100 motion-reduce:transition-none">
            <span class="flex size-4 shrink-0 scale-0 items-center justify-center drop-shadow-[0_1px_1px_rgb(0_0_0_/_0.9)] transition-transform duration-300 ease-out group-data-[state=complete]/scanner:scale-100 group-data-[state=detected]/scanner:scale-100 group-data-[state=success]/scanner:scale-100 motion-reduce:transition-none">
                <x-bladewind::icon name="check-circle" type="solid" size="small" />
            </span>
            <span>{{ __('ui.scanner.scan_success') }}</span>
        </span>
        <span data-barcode-message-announcement class="sr-only">{{ __('ui.scanner.scan_prompt') }}</span>
    </div>
</div>

@once
    @vite('resources/js/barcode-scanner/index.ts')
@endonce
