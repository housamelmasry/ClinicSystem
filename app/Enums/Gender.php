<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel
{
    case Male = 'Male';
    case Female = 'Female';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Male => 'Male',
            self::Female => 'Female',
        };
    }
    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Male  => 'warning',
            self::Female  => 'success',
        };
    }
}
