<?php

namespace Modules\Doctor\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\MedicalExamination;
use Modules\Doctor\Models\VitalSign;

class VitalSignsLivewire extends Component
{
    public mixed $vitalSigns;

    public int $medicalExaminationId;

    public MedicalExamination $medicalExamination;

    public string $componentName = 'VitalSignsLivewire';

    // Bind inputs like values[ID] => '120/80'
    #[Validate('array')]
    public array $values = [];

    public bool $isOpen = true;

    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function saveOne(int $vitalSignId): void
    {
        $value = $this->values[$vitalSignId] ?? null;

        // Block save when the value is numeric AND outside the configured range
        if ($this->isOutOfRange($vitalSignId, $value)) {
            $this->addError(
                "values.{$vitalSignId}",
                trans('doctor::doctor.vitalSign.out_of_range')
            );
            $this->dispatch(
                'toast',
                type: 'error',
                message: __('Value is out of normal range. Not saved.')
            );
            return;
        }

        // Clear any stale validation error for this field before saving
        $this->resetErrorBag("values.{$vitalSignId}");

        // store/update pivot without removing other links
        $this->medicalExamination
            ->vitalSigns()
            ->syncWithoutDetaching([$vitalSignId => ['value' => $value]]);

        $this->dispatch('toast', type: 'success', message: __('Saved.'));
    }

    public function saveAll(): void
    {
        $payload = [];
        $invalid = [];

        foreach ($this->values as $id => $val) {
            $intId = (int) $id;

            if ($this->isOutOfRange($intId, $val)) {
                $invalid[] = $intId;
                $this->addError(
                    "values.{$intId}",
                    trans('doctor::doctor.vitalSign.out_of_range')
                );
                continue;
            }

            $payload[$intId] = ['value' => $val];
        }

        if (! empty($invalid)) {
            $this->dispatch(
                'toast',
                type: 'error',
                message: __('Some vital signs are out of normal range and were not saved.')
            );
            // Don't persist the rest either — keep the bulk save atomic so the
            // doctor explicitly fixes invalid values before re-saving.
            return;
        }

        if (! empty($payload)) {
            $this->medicalExamination->vitalSigns()->syncWithoutDetaching($payload);
        }

        $this->dispatch('toast', type: 'success', message: __('All vital signs saved.'));
    }

    /**
     * Numeric range guard: returns true ONLY for numeric values that fall
     * outside the vital sign's min/max. Non-numeric or empty values are
     * allowed (e.g. blood-pressure "120/80", blood-type "A+", or cleared input).
     */
    protected function isOutOfRange(int $vitalSignId, mixed $value): bool
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return false;
        }

        $vitalSign = VitalSign::find($vitalSignId);
        if (! $vitalSign) {
            return false;
        }

        return $vitalSign->isValueInRange((float) $value) === false;
    }

    public function mount(MedicalExamination $medicalExamination): void
    {
        $this->medicalExamination = $medicalExamination;

        $this->vitalSigns = VitalSign::query()
            ->where('is_active', ActiveEnum::ACTIVE->value)
            ->orderBy('id')
            ->get();

        // prefill from existing pivot values (if editing)
        $existing = $this->medicalExamination
            ->vitalSigns()
            ->pluck('medical_examination_vital_sign.value', 'vital_sign_id')
            ->toArray();

        $this->values = $existing + $this->values; // keep any already-typed inputs
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('doctor::livewire.vital-signs-livewire');
    }
}
