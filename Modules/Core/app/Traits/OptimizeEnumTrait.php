<?php

namespace Modules\Core\app\Traits;

trait OptimizeEnumTrait
{
    public static function getAllEnumValuesKeysLabel(): array
    {
        $cases = self::cases();

        return array_combine(
            array_map(fn ($case) => $case->value, $cases),  // Enum values as keys
            array_map(fn ($case) => $case->label(), $cases), // Labels as values
        );
    }

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
