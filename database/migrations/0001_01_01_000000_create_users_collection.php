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
        Schema::connection('mongodb')->create('users', function (Blueprint $collection): void {
            $collection->unique('email');
        });
        Schema::connection('mongodb')->create('password_reset_tokens', function (Blueprint $collection): void {
            $collection->unique('email');
        });
        Schema::connection('mongodb')->create('sessions', function (Blueprint $collection): void {
            $collection->index('user_id');
            $collection->index('last_activity');
            $collection->expire('expires_at', 0);
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('sessions');
        Schema::connection('mongodb')->dropIfExists('password_reset_tokens');
        Schema::connection('mongodb')->dropIfExists('users');
    }
};
