@php
    $dayLabels = [
        'Monday' => 'Maandag',
        'Tuesday' => 'Dinsdag',
        'Wednesday' => 'Woensdag',
        'Thursday' => 'Donderdag',
        'Friday' => 'Vrijdag',
        'Saturday' => 'Zaterdag',
        'Sunday' => 'Zondag',
    ];
    $formIngredients = old('ingredients', $meal->ingredients ?? [
        ['label' => 'Yoghurt', 'quantity' => 500, 'unit' => 'g'],
    ]);
@endphp

<form action="{{ $action }}" method="POST" class="space-y-7" data-meal-form data-category-url="{{ route('ingredient-categories.index') }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">Controleer de ingevulde gegevens en voeg minimaal één ingrediënt toe.</div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="mb-2 block text-sm font-semibold">Naam van de maaltijd</label>
            <input id="name" name="name" value="{{ old('name', $meal->name) }}" required maxlength="100" placeholder="Bijvoorbeeld Ontbijtprep" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
        </div>
        <div>
            <label for="servings" class="mb-2 block text-sm font-semibold">Aantal porties</label>
            <input id="servings" name="servings" type="number" min="1" max="50" value="{{ old('servings', $meal->servings ?? 4) }}" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
        </div>
        <div>
            <label for="prep_day" class="mb-2 block text-sm font-semibold">Prep-dag</label>
            <select id="prep_day" name="prep_day" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                @foreach ($dayLabels as $day => $label)
                    <option value="{{ $day }}" @selected(old('prep_day', $meal->prep_day ?? 'Sunday') === $day)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <fieldset>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <legend class="text-lg font-bold">Ingrediënten</legend>
                <p class="mt-1 text-sm text-slate-500">Zoek in alle Open Food Facts-categorieën of typ zelf een ingrediënt.</p>
            </div>
            <button type="button" data-add-ingredient class="rounded-xl border border-brand-600 px-4 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-50">Ingrediënt toevoegen</button>
        </div>

        <datalist id="ingredient-categories">
            @foreach ($standardIngredients as $category)
                <option value="{{ $category }}"></option>
            @endforeach
        </datalist>

        <div class="mt-4 space-y-3" data-ingredient-list>
            @foreach ($formIngredients as $index => $ingredient)
                <div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-[minmax(0,1fr)_140px_90px_auto]" data-ingredient-row>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Categorie</label>
                        <input name="ingredients[{{ $index }}][label]" value="{{ $ingredient['label'] ?? '' }}" list="ingredient-categories" required autocomplete="off" placeholder="Zoek bijvoorbeeld rijst" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100" data-category-input>
                        <p class="mt-2 text-xs text-slate-500" data-category-status>Typ om in Open Food Facts te zoeken; eigen categorieën zijn ook toegestaan.</p>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Hoeveelheid</label>
                        <input name="ingredients[{{ $index }}][quantity]" type="number" min="1" step="1" value="{{ $ingredient['quantity'] ?? 100 }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Eenheid</label>
                        <select name="ingredients[{{ $index }}][unit]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                            @foreach (['g', 'ml'] as $unit)
                                <option value="{{ $unit }}" @selected(($ingredient['unit'] ?? 'g') === $unit)>{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" data-remove-ingredient class="w-full rounded-xl px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 sm:w-auto">Verwijder</button>
                    </div>
                </div>
            @endforeach
        </div>

        <template data-ingredient-template>
            <div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-[minmax(0,1fr)_140px_90px_auto]" data-ingredient-row>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Categorie</label>
                    <input name="ingredients[__INDEX__][label]" list="ingredient-categories" required autocomplete="off" placeholder="Zoek bijvoorbeeld rijst" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100" data-category-input>
                    <p class="mt-2 text-xs text-slate-500" data-category-status>Typ om in Open Food Facts te zoeken; eigen categorieën zijn ook toegestaan.</p>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Hoeveelheid</label>
                    <input name="ingredients[__INDEX__][quantity]" type="number" min="1" step="1" value="100" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Eenheid</label>
                    <select name="ingredients[__INDEX__][unit]" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                        <option value="g">g</option>
                        <option value="ml">ml</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" data-remove-ingredient class="w-full rounded-xl px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 sm:w-auto">Verwijder</button>
                </div>
            </div>
        </template>
        <p class="mt-3 text-xs text-slate-500">Bij opslaan vertaalt Laravel de gekozen naam naar de canonieke Open Food Facts-tag.</p>
    </fieldset>

    <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row">
        <button type="submit" class="rounded-xl bg-brand-700 px-5 py-3 font-semibold text-white shadow-sm hover:bg-brand-900">{{ $submitLabel }}</button>
        <a href="{{ route('meals.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-center font-semibold text-slate-700 hover:bg-slate-50">Annuleren</a>
    </div>
</form>
