<?php

namespace Modules\Doctor\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class UploadFileRule implements ValidationRule
{
    public function __construct(
        private string $model,  // e.g. 'patient'
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $class = $this->model;
        if (! $class || ! class_exists($class)) {
            $fail('Invalid model.');

            return;
        }

        if (! $class::whereKey($value)->exists()) {
            $fail('Record not found for the given model.');

            return;
        }
    }
}
