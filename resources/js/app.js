import { BrowserMultiFormatReader } from '@zxing/browser';
import { registerSW } from 'virtual:pwa-register';

registerSW({ immediate: true });

const form = document.querySelector('[data-scan-form]');

if (form) {
    const barcodeInput = form.querySelector('[name="barcode"]');
    const mealInput = form.querySelector('[name="meal_id"]');
    const message = document.querySelector('[data-message]');
    const result = document.querySelector('[data-result]');
    const cameraPanel = document.querySelector('[data-camera-panel]');
    const video = document.querySelector('[data-camera-video]');
    const startCameraButton = document.querySelector('[data-start-camera]');
    const stopCameraButton = document.querySelector('[data-stop-camera]');
    const mealState = document.querySelector('[data-meal-state]');
    let controls = null;

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const formatNumber = (value, suffix = ' g') => value === null || value === undefined
        ? 'Niet beschikbaar'
        : `${Number(value).toFixed(1)}${suffix}`;

    const showMessage = (text, tone = 'error') => {
        message.textContent = text;
        message.className = `rounded-xl border px-4 py-3 text-sm ${tone === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
            : 'border-red-200 bg-red-50 text-red-800'}`;
        message.hidden = false;
    };

    const hideMessage = () => {
        message.hidden = true;
    };

    const matchLabel = (status) => ({
        matched: 'Product past bij de categorie',
        mismatched: 'Product lijkt niet bij de categorie te passen',
        uncertain: 'Classificatie is onzeker',
    })[status] ?? status;

    const renderResult = (payload) => {
        const primary = payload.match.primary;
        const nutrition = payload.nutrition.per_portion;
        const comparisonRows = payload.comparison
            ? Object.entries(payload.comparison)
                .filter(([, value]) => value !== null)
                .map(([key, value]) => `<li><span>${escapeHtml(key.replaceAll('_', ' '))}</span><strong>${value > 0 ? '+' : ''}${Number(value).toFixed(1)}</strong></li>`)
                .join('')
            : '<li class="text-slate-500">Scan nog een product in dezelfde categorie om te vergelijken.</li>';

        result.innerHTML = `
            <div class="grid gap-6 lg:grid-cols-[180px_1fr]">
                ${payload.product.image_url
                    ? `<img class="mx-auto h-44 w-44 rounded-2xl bg-white object-contain p-3 shadow-sm" src="${escapeHtml(payload.product.image_url)}" alt="${escapeHtml(payload.product.product_name)}">`
                    : '<div class="grid h-44 w-44 place-items-center rounded-2xl bg-slate-100 text-sm text-slate-500">Geen productafbeelding</div>'}
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-brand-700">${escapeHtml(payload.product.brands || 'Onbekend merk')}</p>
                    <h2 class="mt-1 text-2xl font-bold">${escapeHtml(payload.product.product_name)}</h2>
                    <div class="mt-4 rounded-xl bg-slate-100 p-4">
                        <p class="font-semibold">${escapeHtml(matchLabel(payload.match.status))}</p>
                        <p class="mt-1 text-sm text-slate-600">Voorspeld: ${escapeHtml(primary.predicted_category || 'onbekend')} · zekerheid ${Math.round(Number(primary.confidence) * 100)}% · ${primary.strategy === 'ml-service' ? 'ML-model' : 'baseline'}</p>
                    </div>
                    <dl class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl border border-slate-200 p-3"><dt class="text-xs text-slate-500">Energie / portie</dt><dd class="mt-1 font-bold">${formatNumber(nutrition.energy_kcal, ' kcal')}</dd></div>
                        <div class="rounded-xl border border-slate-200 p-3"><dt class="text-xs text-slate-500">Eiwit / portie</dt><dd class="mt-1 font-bold">${formatNumber(nutrition.protein)}</dd></div>
                        <div class="rounded-xl border border-slate-200 p-3"><dt class="text-xs text-slate-500">Suiker / portie</dt><dd class="mt-1 font-bold">${formatNumber(nutrition.sugars)}</dd></div>
                        <div class="rounded-xl border border-slate-200 p-3"><dt class="text-xs text-slate-500">NOVA</dt><dd class="mt-1 font-bold">${escapeHtml(payload.product.nova_group ?? 'Onbekend')}</dd></div>
                    </dl>
                </div>
            </div>
            <div class="mt-6 border-t border-slate-200 pt-5">
                <h3 class="font-semibold">Verschil met vorige scan per portie</h3>
                <ul class="mt-3 grid gap-2 text-sm sm:grid-cols-2 comparison-list">${comparisonRows}</ul>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <button type="button" data-accept-scan="${escapeHtml(payload.scan_id)}" class="rounded-xl bg-brand-700 px-5 py-3 font-semibold text-white shadow-sm hover:bg-brand-900">Product gebruiken</button>
                <button type="button" data-scan-another class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50">Ander product scannen</button>
            </div>`;
        result.hidden = false;

        result.querySelector('[data-accept-scan]').addEventListener('click', async (event) => {
            const response = await fetch(`/api/scans/${event.currentTarget.dataset.acceptScan}/accept`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
            const accepted = await response.json();

            if (!response.ok) {
                showMessage(accepted.message || 'Het product kon niet worden opgeslagen.');
                return;
            }

            renderMealState(accepted.demo);
            showMessage(accepted.message, 'success');
        });

        result.querySelector('[data-scan-another]').addEventListener('click', () => {
            barcodeInput.value = '';
            barcodeInput.focus();
            result.hidden = true;
        });
    };

    const renderMealState = (demo) => {
        mealState.innerHTML = `
            <div><span class="text-sm text-slate-500">Prep-score</span><strong class="block text-3xl text-brand-700">${escapeHtml(demo.prep_score.score)}/100</strong></div>
            <div><span class="text-sm text-slate-500">Datavolledigheid</span><strong class="block text-xl">${escapeHtml(demo.prep_score.completeness)}%</strong></div>
            <div><span class="text-sm text-slate-500">Energie / portie</span><strong class="block text-xl">${formatNumber(demo.nutrition.per_portion.energy_kcal, ' kcal')}</strong></div>
            <div><span class="text-sm text-slate-500">Eiwit / portie</span><strong class="block text-xl">${formatNumber(demo.nutrition.per_portion.protein)}</strong></div>`;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        hideMessage();
        result.hidden = true;

        const formData = new FormData(form);
        const submitButton = form.querySelector('[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Product ophalen…';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    meal_id: mealInput.value,
                    barcode: barcodeInput.value.trim(),
                    category: formData.get('category'),
                }),
            });
            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || Object.values(payload.errors ?? {}).flat()[0] || 'Scannen is mislukt.');
            }

            renderResult(payload);
        } catch (error) {
            showMessage(error.message);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Product controleren';
        }
    });

    startCameraButton.addEventListener('click', async () => {
        hideMessage();
        cameraPanel.hidden = false;

        try {
            const reader = new BrowserMultiFormatReader();
            controls = await reader.decodeFromConstraints(
                { video: { facingMode: { ideal: 'environment' } } },
                video,
                (scanResult) => {
                    if (!scanResult) return;

                    barcodeInput.value = scanResult.getText();
                    controls?.stop();
                    cameraPanel.hidden = true;
                    form.requestSubmit();
                },
            );
        } catch (error) {
            cameraPanel.hidden = true;
            showMessage('De camera kon niet worden gestart. Gebruik de handmatige barcode-invoer.');
        }
    });

    stopCameraButton.addEventListener('click', () => {
        controls?.stop();
        cameraPanel.hidden = true;
    });
}

const mealForm = document.querySelector('[data-meal-form]');

if (mealForm) {
    const ingredientList = mealForm.querySelector('[data-ingredient-list]');
    const ingredientTemplate = mealForm.querySelector('[data-ingredient-template]');
    const categoryOptions = mealForm.querySelector('#ingredient-categories');
    const categoryUrl = mealForm.dataset.categoryUrl;
    let categoryRequest = null;
    let categoryTimer = null;

    const rows = () => [...ingredientList.querySelectorAll('[data-ingredient-row]')];

    const reindexRows = () => {
        rows().forEach((row, index) => {
            row.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name.replace(/ingredients\[\d+]/, `ingredients[${index}]`);
            });
        });

        const onlyRow = rows().length === 1;
        rows().forEach((row) => {
            row.querySelector('[data-remove-ingredient]').disabled = onlyRow;
        });
    };

    const updateSuggestions = (suggestions) => {
        const values = new Set([
            ...[...categoryOptions.options].map((option) => option.value),
            ...suggestions,
        ]);

        categoryOptions.replaceChildren(...[...values].map((value) => {
            const option = document.createElement('option');
            option.value = value;

            return option;
        }));
    };

    ingredientList.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-ingredient]');

        if (!removeButton || rows().length === 1) return;

        removeButton.closest('[data-ingredient-row]').remove();
        reindexRows();
    });

    ingredientList.addEventListener('input', (event) => {
        if (!event.target.matches('[data-category-input]')) return;

        const query = event.target.value.trim();
        const status = event.target.closest('[data-ingredient-row]').querySelector('[data-category-status]');
        clearTimeout(categoryTimer);
        categoryRequest?.abort();

        if (query.length < 2) {
            status.textContent = 'Typ om in Open Food Facts te zoeken; eigen categorieën zijn ook toegestaan.';
            return;
        }

        status.textContent = 'Open Food Facts doorzoeken…';

        categoryTimer = setTimeout(async () => {
            categoryRequest = new AbortController();

            try {
                const url = new URL(categoryUrl, window.location.origin);
                url.searchParams.set('q', query);
                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                    signal: categoryRequest.signal,
                });

                if (!response.ok) return;

                const payload = await response.json();
                updateSuggestions(payload.suggestions ?? []);
                status.textContent = payload.taxonomy_match_count > 0
                    ? 'Suggesties gevonden. Je mag ook je eigen invoer gebruiken.'
                    : `Geen exacte OFF-categorie gevonden. “${query}” wordt als eigen categorie opgeslagen.`;
            } catch (error) {
                if (error.name !== 'AbortError') {
                    status.textContent = `OFF is niet bereikbaar. “${query}” wordt als eigen categorie opgeslagen.`;
                }
            }
        }, 400);
    });

    mealForm.querySelector('[data-add-ingredient]').addEventListener('click', () => {
        const index = rows().length;
        const fragment = ingredientTemplate.content.cloneNode(true);

        fragment.querySelectorAll('[name]').forEach((input) => {
            input.name = input.name.replace('__INDEX__', index);
        });

        ingredientList.append(fragment);
        reindexRows();
        rows().at(-1).querySelector('[data-category-input]').focus();
    });

    reindexRows();
}
