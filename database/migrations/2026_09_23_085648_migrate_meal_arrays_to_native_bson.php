<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::connection('mongodb')
            ->table('meals')
            ->get()
            ->each(function (object $meal): void {
                $updates = collect(['ingredients', 'selections'])
                    ->mapWithKeys(function (string $field) use ($meal): array {
                        $value = data_get($meal, $field);

                        return is_string($value)
                            ? [$field => json_decode($value, true, flags: JSON_THROW_ON_ERROR)]
                            : [];
                    })
                    ->all();

                if ($updates !== []) {
                    DB::connection('mongodb')
                        ->table('meals')
                        ->where('id', data_get($meal, 'id'))
                        ->update($updates);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('mongodb')
            ->table('meals')
            ->get()
            ->each(function (object $meal): void {
                $updates = collect(['ingredients', 'selections'])
                    ->mapWithKeys(function (string $field) use ($meal): array {
                        $value = data_get($meal, $field);

                        return is_array($value)
                            ? [$field => json_encode($value, JSON_THROW_ON_ERROR)]
                            : [];
                    })
                    ->all();

                if ($updates !== []) {
                    DB::connection('mongodb')
                        ->table('meals')
                        ->where('id', data_get($meal, 'id'))
                        ->update($updates);
                }
            });
    }
};
