<?php

namespace Modules\Doctor\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Core\app\Traits\OptimizeLivewireTrait;
use Modules\Doctor\Models\FinalDiagnosis;
use Modules\Doctor\Models\FinalDiagnosisPatient;
use Modules\Doctor\Models\MedicalExamination;
use Modules\Doctor\Models\Patient;
use Throwable;

class FinalDiagnosisPatientLivewire extends Component
{
    use OptimizeLivewireTrait;

    public string $onChangeEvent;

    public string $componentName;

    public int $patientId;

    public Patient $patient;

    public MedicalExamination $medicalExamination;

    public mixed $finalDiagnosis;

    public mixed $addedFinalDiagnosis;

    public string $name;

    /**
     * @throws Throwable
     */
    #[On('finalDiagnoseUpdated')]
    public function finalDiagnoseUpdated($value): void
    {
        if ($value) {
            $this->addedFinalDiagnosis[] = $value;
            $this->medicalExamination
                ->finalDiagnosis()
                ->syncWithPivotValues($value, [
                    'patient_id' => $this->patientId,
                    'medical_examination_id' => $this->medicalExamination->id,
                ]);
            $this->dispatch('initSelect2');
        }
        $this->dispatch('reRenderSelect2');
    }

    public function mount(MedicalExamination $medicalExamination, $patientId): void
    {
        $this->medicalExamination = $medicalExamination;
        $this->patient = Patient::query()->find($patientId);
        $this->patientId = $patientId;
        $this->onChangeEvent = 'finalDiagnoseUpdated';
        $this->componentName = 'FinalDiagnosisPatientLivewire';

        $this->finalDiagnosis = FinalDiagnosis::getFinalDiagnosisSelect2();

        $addedFinalDiagnosis = FinalDiagnosisPatient::query()
            ->where('medical_examination_id', $this->medicalExamination->id)
            ->pluck('final_diagnosis_id')->toArray();
        $this->addedFinalDiagnosis = $addedFinalDiagnosis;
        $this->dispatch('reRenderSelect2');
        $this->addValueToSelect2('finalDiagnose', $this->addedFinalDiagnosis, true);
    }

    public function render(): Factory|View
    {
        return view('doctor::livewire.final-diagnosis-patient-livewire');
    }
}
