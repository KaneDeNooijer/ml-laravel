<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::connection('mongodb')->create('cache', function (Blueprint $collection): void {
            $collection->expire('expires_at', 0);
        });
        Schema::connection('mongodb')->create('cache_locks', function (Blueprint $collection): void {
            $collection->expire('expires_at', 0);
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('cache_locks');
        Schema::connection('mongodb')->dropIfExists('cache');
    }
};
