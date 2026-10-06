<form action="{{ $action }}" method="POST" class="space-y-7" data-meal-form>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <x-bladewind::alert type="error" :show_close_icon="false">{{ __('ui.meal_form.error') }}</x-bladewind::alert>
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-bladewind::input name="name" label="{{ __('ui.meal_form.name') }}" placeholder="{{ __('ui.meal_form.name_placeholder') }}" :selected_value="old('name', $meal->name)" maxlength="100" required="true" show_validation_error="true" />
        </div>
        <x-bladewind::input name="servings" label="{{ __('ui.meal_form.servings') }}" :selected_value="old('servings', $meal->servings)" numeric="true" min="1" max="50" required="true" show_validation_error="true" />
        <div class="[&_.bw-select-item]:text-slate-900 [&_.bw-select-item:hover]:text-white [&_.display-area]:text-slate-900">
            <x-bladewind::select name="prep_day_select" data="manual" data_serialize_as="prep_day" label="{{ __('ui.meal_form.prep_day') }}" :selected_value="old('prep_day', $meal->prep_day?->value)" required="true" show_validation_error="true">
                @foreach ($prepDays as $day => $label)
                    <x-bladewind::select.item value="{{ $day }}" label="{{ $label }}" />
                @endforeach
            </x-bladewind::select>
        </div>
    </div>

    <fieldset>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <legend class="text-lg font-bold">{{ __('ui.meal_form.ingredients') }}</legend>
            <x-bladewind::button class="w-full sm:w-auto" type="secondary" data-add-ingredient icon="plus" size="small" radius="medium" uppercasing="false">{{ __('ui.meal_form.add_ingredient') }}</x-bladewind::button>
        </div>

        <div class="mt-4 space-y-3" data-ingredient-list>
            @foreach (old('ingredients', $meal->ingredients) as $index => $ingredient)
                <x-bladewind::card compact="true" :has_shadow="false" data-ingredient-row>
                    <div class="grid grid-cols-1 items-end gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_140px_150px_auto]">
                        <div class="sm:col-span-2 lg:col-span-1">
                            <x-bladewind::input name="ingredient_label_{{ $index }}" label="{{ __('ui.meal_form.ingredient') }}" :selected_value="$ingredient['label'] ?? ''" placeholder="{{ __('ui.meal_form.ingredient_placeholder') }}" autocomplete="off" required="true" :add_clearing="false" />
                        </div>
                        <div>
                            <x-bladewind::input name="ingredient_quantity_{{ $index }}" label="{{ __('ui.meal_form.quantity') }}" :selected_value="$ingredient['quantity'] ?? null" numeric="true" required="true" :add_clearing="false" />
                        </div>
                        <div class="pb-1">
                            <span class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.meal_form.unit') }}</span>
                            <div class="flex gap-2">
                                @foreach (['g', 'ml'] as $unit)
                                    <x-bladewind::radio name="ingredient_unit_{{ $index }}" value="{{ $unit }}" label="{{ $unit }}" :checked="($ingredient['unit'] ?? null) === $unit" :add_clearing="false" />
                                @endforeach
                            </div>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-1">
                            <x-bladewind::button class="w-full lg:w-auto" type="secondary" color="red" outline="true" data-remove-ingredient icon="trash" size="small" radius="medium" uppercasing="false">{{ __('ui.common.delete') }}</x-bladewind::button>
                        </div>
                    </div>
                </x-bladewind::card>
            @endforeach
        </div>

        <template data-ingredient-template>
            <x-bladewind::card compact="true" :has_shadow="false" data-ingredient-row>
                <div class="grid grid-cols-1 items-end gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_140px_150px_auto]">
                    <div class="sm:col-span-2 lg:col-span-1">
                        <x-bladewind::input name="ingredient_label___INDEX__" label="{{ __('ui.meal_form.ingredient') }}" placeholder="{{ __('ui.meal_form.ingredient_placeholder') }}" autocomplete="off" required="true" :add_clearing="false" />
                    </div>
                    <div>
                        <x-bladewind::input name="ingredient_quantity___INDEX__" label="{{ __('ui.meal_form.quantity') }}" numeric="true" required="true" :add_clearing="false" />
                    </div>
                    <div class="pb-1">
                        <span class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.meal_form.unit') }}</span>
                        <div class="flex gap-2">
                            <x-bladewind::radio name="ingredient_unit___INDEX__" value="g" label="g" :add_clearing="false" />
                            <x-bladewind::radio name="ingredient_unit___INDEX__" value="ml" label="ml" :add_clearing="false" />
                        </div>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <x-bladewind::button class="w-full lg:w-auto" type="secondary" color="red" outline="true" data-remove-ingredient icon="trash" size="small" radius="medium" uppercasing="false">{{ __('ui.common.delete') }}</x-bladewind::button>
                    </div>
                </div>
            </x-bladewind::card>
        </template>
    </fieldset>

    <x-bladewind::divider spacing="small" />
    <div class="flex flex-col gap-3 sm:flex-row">
        <x-bladewind::button class="w-full sm:w-auto" can_submit="true" type="secondary" icon="check" radius="medium" uppercasing="false">{{ $submitLabel }}</x-bladewind::button>
        <x-bladewind::button class="w-full sm:w-auto" tag="a" href="{{ route('meals.index') }}" type="secondary" icon="arrow-left" radius="medium" uppercasing="false">{{ __('ui.common.cancel') }}</x-bladewind::button>
    </div>
</form>
