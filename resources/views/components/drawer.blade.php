@props([
    'contentId' => 'drawer-content',
    'initialState' => 'split',
    'label',
    'handleLabel',
])

<div
    {{ $attributes->class(['group/drawer relative min-h-0 overflow-hidden']) }}
    data-drawer
    data-drawer-state="{{ $initialState }}"
>
    <div
        data-drawer-background
        class="absolute inset-x-0 top-0 transition-[height,opacity] duration-300 ease-out group-data-[drawer-dragging=true]/drawer:transition-none group-data-[drawer-state=collapsed]/drawer:h-full group-data-[drawer-state=expanded]/drawer:pointer-events-none group-data-[drawer-state=expanded]/drawer:h-0 group-data-[drawer-state=expanded]/drawer:opacity-0 group-data-[drawer-state=split]/drawer:h-1/3 motion-reduce:transition-none"
    >
        {{ $background }}
    </div>

    <section
        data-drawer-panel
        class="absolute inset-x-0 bottom-0 z-20 overflow-hidden border-t border-slate-200 bg-white shadow-2xl transition-[top] duration-300 ease-out group-data-[drawer-dragging=true]/drawer:transition-none group-data-[drawer-state=collapsed]/drawer:top-[calc(100%-2.5rem)] group-data-[drawer-state=expanded]/drawer:top-0 group-data-[drawer-state=split]/drawer:top-1/3 motion-reduce:transition-none"
        aria-label="{{ $label }}"
    >
        <button
            data-drawer-handle
            type="button"
            class="flex h-10 w-full touch-none cursor-grab items-center justify-center active:cursor-grabbing"
            aria-label="{{ $handleLabel }}"
            aria-controls="{{ $contentId }}"
            aria-expanded="{{ $initialState === 'expanded' ? 'true' : 'false' }}"
        >
            <span class="h-1.5 w-12 rounded-full bg-slate-300" aria-hidden="true"></span>
        </button>

        <div
            id="{{ $contentId }}"
            data-drawer-content
            class="h-[calc(100%-2.5rem)] overflow-y-auto group-data-[drawer-state=collapsed]/drawer:invisible"
        >
            {{ $slot }}
        </div>
    </section>
</div>

@once
    @vite('resources/js/drawer/index.ts')
@endonce
