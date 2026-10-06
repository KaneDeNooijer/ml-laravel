<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final readonly class ScannerController
{
    public function index(): View
    {
        return view('scanner.index');
    }
}
