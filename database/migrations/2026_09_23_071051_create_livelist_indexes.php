<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mongodb')->create('meals', function (Blueprint $collection): void {
            $collection->unique('slug');
        });

        Schema::connection('mongodb')->create('product_snapshots', function (Blueprint $collection): void {
            $collection->unique('barcode');
        });

        Schema::connection('mongodb')->create('scan_events', function (Blueprint $collection): void {
            $collection->index(['meal_id' => 1, 'category' => 1, 'created_at' => -1]);
        });

        Schema::connection('mongodb')->create('predictions', function (Blueprint $collection): void {
            $collection->index('scan_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('predictions');
        Schema::connection('mongodb')->dropIfExists('scan_events');
        Schema::connection('mongodb')->dropIfExists('product_snapshots');
        Schema::connection('mongodb')->dropIfExists('meals');
    }
};
