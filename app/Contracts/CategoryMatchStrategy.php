<?php

namespace App\Contracts;

interface CategoryMatchStrategy
{
    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    public function match(array $product, string $expectedCategory): array;
}
