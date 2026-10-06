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
        Schema::connection('mongodb')->create('jobs', function (Blueprint $collection): void {
            $collection->index(['queue' => 1, 'reserved' => 1, 'available_at' => 1]);
            $collection->index('reserved_at');
        });
        Schema::connection('mongodb')->create('job_batches', function (Blueprint $collection): void {});
        Schema::connection('mongodb')->create('failed_jobs', function (Blueprint $collection): void {
            $collection->unique('uuid');
            $collection->index('failed_at');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('failed_jobs');
        Schema::connection('mongodb')->dropIfExists('job_batches');
        Schema::connection('mongodb')->dropIfExists('jobs');
    }
};
