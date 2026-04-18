<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * Shared base for API form-request classes that need to return a JSON
 * error envelope when validation fails.
 *
 * The previous version pulled `validationErrorResponse()` from a
 * `Modules\API\Traits\ApiResponse` trait which never existed in the
 * codebase, making this class throw a "trait not found" error the moment
 * anything tried to extend it. The helper is now inlined.
 */
abstract class BaseFormRequest extends FormRequest
{
    /**
     * Return the standard API validation error envelope.
     *
     * @param  array<string, array<int, string>>  $errors
     */
    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return new JsonResponse([
            'status' => false,
            'message' => 'The given data was invalid.',
            'errors' => $errors,
        ], 422);
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                $this->validationErrorResponse($validator->errors()->toArray()),
            );
        }

        parent::failedValidation($validator);
    }
}
