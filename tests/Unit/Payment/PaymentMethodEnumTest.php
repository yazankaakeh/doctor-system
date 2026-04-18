<?php

namespace Tests\Unit\Payment;

use Modules\Payment\Enums\PaymentMethodEnum;
use PHPUnit\Framework\TestCase;

class PaymentMethodEnumTest extends TestCase
{
    /**
     * @test
     */
    public function it_exposes_the_three_supported_payment_methods(): void
    {
        $values = array_map(fn (PaymentMethodEnum $c) => $c->value, PaymentMethodEnum::cases());

        $this->assertContains('paypal', $values);
        $this->assertContains('offline', $values);
        $this->assertContains('credit_card', $values, 'CREDIT_CARD case must be registered.');
    }

    /**
     * @test
     */
    public function credit_card_case_uses_the_credit_card_icon(): void
    {
        $this->assertSame('credit-card', PaymentMethodEnum::CREDIT_CARD->icon());
    }

    /**
     * @test
     */
    public function paypal_case_uses_the_paypal_icon(): void
    {
        $this->assertSame('paypal', PaymentMethodEnum::PAYPAL->icon());
    }

    /**
     * @test
     */
    public function offline_case_uses_the_building_bank_icon(): void
    {
        $this->assertSame('building-bank', PaymentMethodEnum::OFFLINE->icon());
    }

    /**
     * @test
     */
    public function credit_card_is_a_valid_try_from_result(): void
    {
        $this->assertSame(
            PaymentMethodEnum::CREDIT_CARD,
            PaymentMethodEnum::tryFrom('credit_card'),
        );
        $this->assertNull(PaymentMethodEnum::tryFrom('not-a-method'));
    }
}
