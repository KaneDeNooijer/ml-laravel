<?php

namespace App\Services;

final class PrepScoreCalculator
{
    /**
     * @param  array<string, float|null>  $perPortion
     * @return array<string, mixed>
     */
    public function calculate(array $perPortion, ?int $novaGroup): array
    {
        $known = collect(['energy_kcal', 'protein', 'saturated_fat', 'sugars', 'salt'])
            ->filter(fn (string $key): bool => $perPortion[$key] !== null)
            ->count();
        $completeness = (int) round((($known + ($novaGroup !== null ? 1 : 0)) / 6) * 100);
        $proteinBonus = min(15, ((float) ($perPortion['protein'] ?? 0)) * 0.75);
        $sugarPenalty = min(15, ((float) ($perPortion['sugars'] ?? 0)) * 0.75);
        $saturatedFatPenalty = min(15, ((float) ($perPortion['saturated_fat'] ?? 0)) * 1.5);
        $saltPenalty = min(15, ((float) ($perPortion['salt'] ?? 0)) * 8);
        $energyPenalty = min(10, max(0, (((float) ($perPortion['energy_kcal'] ?? 0)) - 600) / 40));
        $novaPenalty = [1 => 0, 2 => 4, 3 => 8, 4 => 15][$novaGroup] ?? 0;
        $score = max(0, min(100, 70 + $proteinBonus - $sugarPenalty - $saturatedFatPenalty - $saltPenalty - $energyPenalty - $novaPenalty));

        return [
            'score' => (int) round($score),
            'completeness' => $completeness,
            'is_partial' => $completeness < 100,
            'factors' => [
                'protein_bonus' => round($proteinBonus, 1),
                'sugar_penalty' => round($sugarPenalty, 1),
                'saturated_fat_penalty' => round($saturatedFatPenalty, 1),
                'salt_penalty' => round($saltPenalty, 1),
                'energy_penalty' => round($energyPenalty, 1),
                'nova_penalty' => $novaPenalty,
            ],
        ];
    }
}
