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
        // validate only this one if you need stricter rules per sign
        // $this->validate([
        //     "values.$vitalSignId" => ['nullable','string','max:255'], // or numeric
        // ]);

        $value = $this->values[$vitalSignId] ?? null;

        // store/update pivot without removing other links
        $this->medicalExamination
            ->vitalSigns()
            ->syncWithoutDetaching([$vitalSignId => ['value' => $value]]);

        $this->dispatch('toast', type: 'success', message: __('Saved.'));
    }

    public function saveAll(): void
    {
        // Optional validation for all:
        // $this->validate(['values.*' => ['nullable','string','max:255']]);

        // Build payload: [id => ['value' => '...'], ...]
        $payload = [];
        foreach ($this->values as $id => $val) {
            $payload[(int) $id] = ['value' => $val];
        }

        if (! empty($payload)) {
            $this->medicalExamination->vitalSigns()->syncWithoutDetaching($payload);
        }

        $this->dispatch('toast', type: 'success', message: __('All vital signs saved.'));
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
