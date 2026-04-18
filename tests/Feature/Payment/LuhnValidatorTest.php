<?php

namespace Tests\Feature\Payment;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LuhnValidatorTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider validLuhnCards
     */
    public function valid_card_numbers_pass_the_luhn_rule(string $cardNumber): void
    {
        $validator = Validator::make(
            ['card' => $cardNumber],
            ['card' => ['required', 'luhn']],
        );

        $this->assertTrue(
            $validator->passes(),
            "Expected `{$cardNumber}` to pass the luhn rule but it failed: ".$validator->errors()->first(),
        );
    }

    /**
     * @test
     *
     * @dataProvider invalidLuhnCards
     */
    public function invalid_card_numbers_fail_the_luhn_rule(string $cardNumber): void
    {
        $validator = Validator::make(
            ['card' => $cardNumber],
            ['card' => ['required', 'luhn']],
        );

        $this->assertFalse(
            $validator->passes(),
            "Expected `{$cardNumber}` to fail the luhn rule but it passed.",
        );
    }

    /**
     * @test
     */
    public function spaces_in_card_numbers_do_not_break_validation(): void
    {
        // The prepareForValidation step in CreditCardPaymentRequest strips
        // spaces, but the raw validator should also tolerate them because
        // users paste card numbers in many formats.
        $validator = Validator::make(
            ['card' => '4242 4242 4242 4242'],
            ['card' => ['required', 'luhn']],
        );

        $this->assertTrue($validator->passes());
    }

    public static function validLuhnCards(): array
    {
        return [
            'visa test card' => ['4242424242424242'],
            'mastercard test card' => ['5555555555554444'],
            'amex test card' => ['378282246310005'],
            'discover test card' => ['6011111111111117'],
            'visa debit' => ['4000056655665556'],
            // Classic "generic decline" — still Luhn-valid, the app rejects
            // it at the business-logic layer, not at validation.
            'stripe decline card' => ['4000000000000002'],
        ];
    }

    public static function invalidLuhnCards(): array
    {
        return [
            'random digits' => ['1234567890123456'],
            'valid digits +1' => ['4242424242424241'],
            'short number' => ['4242'],
            'non-digits' => ['ABCD5678ABCD5678'],
            'empty-ish' => ['000000'],
        ];
    }
}
