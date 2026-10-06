<?php

declare(strict_types=1);
use App\Providers\AppServiceProvider;
use MongoDB\Laravel\MongoDBBusServiceProvider;

return [
    AppServiceProvider::class,
    MongoDBBusServiceProvider::class,
];
