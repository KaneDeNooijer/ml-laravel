<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::connection('mongodb')->create('meals');
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('meals');
    }
};
