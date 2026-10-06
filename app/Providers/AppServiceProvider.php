<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Input is constrained at the request boundary, so models do not maintain
        // a second and easily outdated list of mass-assignable attributes.
        Model::unguard();
        Model::shouldBeStrict();
    }
}
