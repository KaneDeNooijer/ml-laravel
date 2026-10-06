<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

final readonly class HealthController
{
    public function index(): Response
    {
        return response()->noContent();
    }
}
