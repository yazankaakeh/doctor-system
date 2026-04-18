<?php

namespace Modules\Core\App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class OldPasswordConfirmedRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var User $user */
        $user = auth()->user();
        if (! Hash::check($value, $user->password)) {
            $fail(trans('core::core.validation.password_mismatch'));
        }
    }
}
