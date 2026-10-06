<?php

declare(strict_types=1);

namespace App\Enums;

enum PrepDay: string
{
    case Monday = 'Monday';
    case Tuesday = 'Tuesday';
    case Wednesday = 'Wednesday';
    case Thursday = 'Thursday';
    case Friday = 'Friday';
    case Saturday = 'Saturday';
    case Sunday = 'Sunday';

    public function label(): string
    {
        return __('ui.days.'.$this->value);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $day) {
            $options[$day->value] = $day->label();
        }

        return $options;
    }
}
