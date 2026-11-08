<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PythonService;
use App\Services\PythonServiceException;
use Illuminate\Contracts\View\View;

final readonly class ProductController
{
    public function show(string $product, PythonService $pythonService): View
    {
        try {
            $details = $pythonService->findProduct($product);
            $error = $details === null ? __('ui.products.not_found') : null;
        } catch (PythonServiceException $exception) {
            report($exception);
            $details = null;
            $error = __('ui.products.unavailable');
        }

        return view('products.show', ['barcode' => $product, 'product' => $details, 'error' => $error]);
    }
}
