<?php

namespace Modules\AdminManagement\Tests\Unit;

use Modules\AdminManagement\Enums\ActiveAdminEnum;
use PHPUnit\Framework\TestCase;

class ActiveAdminEnumTest extends TestCase
{
    public function test_active_enum_has_correct_value(): void
    {
        $this->assertEquals(1, ActiveAdminEnum::ACTIVE->value);
    }

    public function test_deactive_enum_has_correct_value(): void
    {
        $this->assertEquals(0, ActiveAdminEnum::DE_ACTIVE->value);
    }

    public function test_active_class_returns_bg_primary(): void
    {
        $this->assertEquals('bg-primary', ActiveAdminEnum::ACTIVE->class());
    }

    public function test_deactive_class_returns_bg_danger(): void
    {
        $this->assertEquals('bg-danger', ActiveAdminEnum::DE_ACTIVE->class());
    }

    public function test_enum_cases_count(): void
    {
        $this->assertCount(2, ActiveAdminEnum::cases());
    }

    public function test_enum_can_be_created_from_value(): void
    {
        $active = ActiveAdminEnum::from(1);
        $deactive = ActiveAdminEnum::from(0);

        $this->assertEquals(ActiveAdminEnum::ACTIVE, $active);
        $this->assertEquals(ActiveAdminEnum::DE_ACTIVE, $deactive);
    }

    public function test_enum_try_from_returns_null_for_invalid_value(): void
    {
        $result = ActiveAdminEnum::tryFrom(99);

        $this->assertNull($result);
    }
}
