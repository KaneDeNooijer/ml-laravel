/**
 * Application-level browser behavior that is not owned by a reusable Blade
 * component. Currently this module manages dynamic meal ingredient rows.
 */

/** Form controls whose names are reindexed before Laravel receives them. */
type NamedFormControl = HTMLInputElement | HTMLSelectElement;

/**
 * Finds required markup and reports a useful selector if Blade and TypeScript
 * drift apart.
 *
 * @param root Element containing the required descendant.
 * @param selector Selector relative to the root.
 */
function requiredElement<T extends Element>(root: ParentNode, selector: string): T {
    const element = root.querySelector<T>(selector);

    if (element === null) {
        throw new Error(`Application element is missing: ${selector}`);
    }

    return element;
}

const mealForm = document.querySelector<HTMLFormElement>('[data-meal-form]');

if (mealForm !== null) {
    const ingredientList = requiredElement<HTMLElement>(mealForm, '[data-ingredient-list]');
    const ingredientTemplate = requiredElement<HTMLTemplateElement>(mealForm, '[data-ingredient-template]');
    const addIngredient = requiredElement<HTMLButtonElement>(mealForm, '[data-add-ingredient]');

    /** Returns the current ingredient rows in their visual order. */
    const rows = (): HTMLElement[] => [
        ...ingredientList.querySelectorAll<HTMLElement>('[data-ingredient-row]'),
    ];

    /**
     * Aligns every row's field names, IDs and labels with its current position.
     * It also prevents the final remaining ingredient from being removed.
     */
    const reindexRows = (): void => {
        rows().forEach((row, index) => {
            row.querySelectorAll<NamedFormControl>('[name]').forEach((input) => {
                input.name = input.name.replace(
                    /ingredient_(label|quantity|unit)_(?:\d+|__INDEX__)/,
                    `ingredient_$1_${index}`,
                );
            });
            row.querySelectorAll<HTMLElement>('[id]').forEach((input) => {
                input.id = input.id.replace(
                    /ingredient_(label|quantity|unit)_(?:\d+|__INDEX__)/,
                    `ingredient_$1_${index}`,
                );
            });
            row.querySelectorAll<HTMLLabelElement>('label[for]').forEach((label) => {
                label.htmlFor = label.htmlFor.replace(
                    /ingredient_(label|quantity|unit)_(?:\d+|__INDEX__)/,
                    `ingredient_$1_${index}`,
                );
            });
        });

        const onlyRow = rows().length === 1;

        rows().forEach((row) => {
            requiredElement<HTMLButtonElement>(row, '[data-remove-ingredient]').disabled = onlyRow;
        });
    };

    /**
     * Removes the clicked ingredient row while preserving at least one row.
     *
     * @param event Click delegated from the ingredient list.
     */
    const removeIngredient = (event: MouseEvent): void => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const removeButton = event.target.closest<HTMLButtonElement>('[data-remove-ingredient]');

        if (removeButton === null || rows().length === 1) {
            return;
        }

        removeButton.closest<HTMLElement>('[data-ingredient-row]')?.remove();
        reindexRows();
    };

    /** Adds and focuses a fresh ingredient row from the Blade template. */
    const addIngredientRow = (): void => {
        ingredientList.append(ingredientTemplate.content.cloneNode(true));
        reindexRows();
        rows().at(-1)?.querySelector<HTMLInputElement>('input[name^="ingredient_label_"]')?.focus();
    };

    ingredientList.addEventListener('click', removeIngredient);
    addIngredient.addEventListener('click', addIngredientRow);
    reindexRows();
}
