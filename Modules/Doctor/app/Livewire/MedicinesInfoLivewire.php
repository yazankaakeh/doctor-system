<?php

namespace Modules\Doctor\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Core\app\Traits\OptimizeLivewireTrait;
use Modules\Doctor\Models\DosageForm;
use Modules\Doctor\Models\MedicalExamination;
use Modules\Doctor\Models\MedicalExaminationMedicine;
use Modules\Doctor\Models\Medicine;

class MedicinesInfoLivewire extends Component
{
    use OptimizeLivewireTrait;

    public string $componentName = 'MedicinesInfoLivewire';

    public mixed $medicines;

    public mixed $medicinesArray = [0];

    public mixed $dosageForms;

    public MedicalExamination $medicalExamination;

    public array $medicinesData = [
        [
            'medicine_id' => null,     // required | exists:medicines,id   (select2)
            'dose' => null,     // required | string | max:100
            'dosage' => null,     // required | string | max:255
            'dosage_form_id' => null,     // required | integer|min:1|max:1000
            'duration' => null,     // nullable | string | max:500
            'note' => null,     // nullable | string | max:500
        ],
    ];

    public function increase(): void
    {
        $this->medicinesData[] = [
            'medicine_id' => null,
            'dose' => null,
            'dosage' => null,
            'dosage_form_id' => null,
            'duration' => null,
            'note' => null,
        ];
        $this->dispatch('reRenderSelect2');
        $this->dispatch('initSelect2');
    }

    public function mount(MedicalExamination $medicalExamination): void
    {
        $this->medicalExamination = $medicalExamination;
        $medicinesData = MedicalExaminationMedicine::query()->where([
            'medical_examination_id' => $this->medicalExamination->id,
        ])->get()->toArray();
        $this->dosageForms = DosageForm::getDosageFormSelect2();
        if (! empty($medicinesData)) {
            $this->medicinesData = MedicalExaminationMedicine::query()->where([
                'medical_examination_id' => $this->medicalExamination->id,
            ])->get()->toArray();
        }
    }

    public function decrease($index): void
    {
        $this->dispatch('reRenderSelect2');
        $this->dispatch('initSelect2');
        if (count($this->medicinesData) == 1) {
            return;
        }
        unset($this->medicinesData[$index]);
        $this->medicinesData = array_values($this->medicinesData);
    }

    public function save(): void
    {
        $validated = $this->validate();

        $syncPayload = [];

        foreach ($validated['medicinesData'] as $row) {
            $drugId = (int) $row['medicine_id'];
            $syncPayload[$drugId] = [
                'medicine_id' => $row['medicine_id'],
                'dose' => $row['dose'],
                'dosage' => $row['dosage'],
                'dosage_form_id' => $row['dosage_form_id'],
                'duration' => $row['duration'],
                'note' => $row['note'],
            ];
        }

        $this->medicalExamination->medicines()->sync($syncPayload);

        $this->dispatch('toast', type: 'success', message: 'Saved & replaced file if existed ✅');
    }

    public function render(): Factory|View
    {
        $this->medicines = Medicine::getMedicinesSelect2();

        return view('doctor::livewire.medicines-info-livewire');
    }

    protected function rules(): array
    {
        return [
            'medicinesData' => ['required', 'array', 'min:1'],
            'medicinesData.*.medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'medicinesData.*.dose' => ['required', 'string', 'max:100'],
            'medicinesData.*.dosage' => ['required', 'string', 'max:100'],
            'medicinesData.*.dosage_form_id' => ['required', 'exists:dosage_forms,id'],
            'medicinesData.*.duration' => ['required', 'string', 'min:1', 'max:1000'],
            'medicinesData.*.note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
