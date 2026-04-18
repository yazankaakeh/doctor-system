<?php

namespace Modules\Core\app\Traits;

use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;

trait OptimizeLivewireTrait
{
    /*public function updated($propertyName): void
    {
      $this->resetErrorBag($propertyName);
      //  $this->dispatch('reRenderSelect2');
      $this->validateOnly($propertyName);

    }*/

    public function getValidationErrorsHandled(ValidationException $e): string
    {
        $errors = array_slice($e->validator->errors()->all(), 0, 5); // Get first 5 errors
        $this->setErrorBag($e->validator->errors());

        return nl2br(implode("\n", $errors)); // Convert to HTML format
    }

    public function addValueToSelect($id, $value): void
    {
        $this->dispatch('addValueToSelect', [
            'id' => $id,
            'value' => $value,
        ]);
        $this->dispatch('reRenderSelect2');
    }

    public function addValueToSelect2($id, $value, $multiple = false): void
    {
        $this->dispatch('addValueToSelect2', [
            'id' => $id,
            'value' => $value,
            'multiple' => $multiple,
        ]);
        $this->dispatch('reRenderSelect2');
    }

    #[On('recaptchaUpdated_{componentName}')]
    public function updateToken($token): void
    {
        $this->recaptcha = $token;
        $this->dispatch('reRenderSelect2');
    }

    #[On('updateSelect2_{componentName}')]
    public function updateSelect2Values($attribute, $value): void
    {
        if ($value) {
            $property = $attribute;
            $values = $value;

            // Dynamically set the nested property
            data_set($this, $property, $values);
            $this->resetErrorBag($property);
        }
        /* if ($attribute == 'city_id' || $attribute == 'newGender') */
        $this->dispatch('initSelect2');
        $this->dispatch('reRenderSelect2');
        // Optional: Trigger reinitialization of select2 or any other actions

    }

    #[On('updateSelectPicker_{componentName}')]
    public function updateSelectPicker($attribute, $value): void
    {
        if ($value) {
            $property = $attribute;
            $values = $value;

            // Dynamically set the nested property
            data_set($this, $property, $values);
            $this->resetErrorBag($property);
        }

        $this->dispatch('reRenderSelect2');
        // Optional: Trigger reinitialization of select2 or any other actions

    }

    #[On('updateDatePicker_{componentName}')]
    public function updateDatePicker($attribute, $value): void
    {
        if ($value) {
            $property = $attribute;
            $values = $value;

            // Dynamically set the nested property
            data_set($this, $property, $values);
            $this->resetErrorBag($property);
        }
        // Optional: Trigger reinitialization of select2 or any other actions
        $this->dispatch('reRenderSelect2');
    }
}
