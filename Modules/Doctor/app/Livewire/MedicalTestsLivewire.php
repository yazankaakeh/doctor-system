<?php

namespace Modules\Doctor\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Core\app\Traits\OptimizeLivewireTrait;
use Modules\Doctor\Enums\MedicalTestTypeEnum;
use Modules\Doctor\Models\MedicalExamination;
use Modules\Doctor\Models\MedicalTest;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class MedicalTestsLivewire extends Component
{
    use OptimizeLivewireTrait, WithFileUploads;

    public string $title;

    public string $onChangeEvent;

    public string $componentName;

    public MedicalExamination $medicalExamination;

    public mixed $medicalTests;

    public mixed $addedMedicalTests;

    public string $name;

    public MedicalTestTypeEnum $type;

    public mixed $listMedicalTests = [];

    /**
     * @throws Throwable
     */
    public function submit(): void
    {
        $ids = $this->listMedicalTests;
        $this->syncTestsForType(
            $this->medicalExamination,
            $this->type->value,
            $ids,  // array of IDs the user selected for LAB
        );
        $this->updateMedicalTests();
    }

    /**
     * Remove a single test from the medical examination.
     *
     * @throws Throwable
     */
    public function removeTest(int $testId): void
    {
        $this->listMedicalTests = array_values(
            array_filter($this->listMedicalTests, fn ($id) => (int) $id !== $testId)
        );
        $this->syncTestsForType(
            $this->medicalExamination,
            $this->type->value,
            $this->listMedicalTests,
        );
        $this->updateMedicalTests();
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Throwable
     */
    /*#[NoReturn]
    public function saveMedicalTestDetails($id): void
    {
        // Validate only this row
        $this->validate([
            "listMedicalTestsValues.$id.value" => ['nullable', 'string', 'max:255'],
            "listMedicalTestsValues.$id.file" => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);
        $row = $this->listMedicalTestsValues[$id];
        if (!empty($row['file'])) {
            $medicalTestExamination = MedicalExaminationMedicalTest::query()
                ->find($id);
            $medicalTestExamination
                ->addMedia($row['file'])
                ->toMediaCollection('attachment');
            $medicalTestExamination->value = $row['value'];
            $medicalTestExamination->save();
        }

        $this->dispatch('toast', type: 'success', message: 'Saved & replaced file if existed ✅');
    }*/
    /**
     * @throws Throwable
     */
    public function syncTestsForType(MedicalExamination $exam, string $typeValue, array $ids): void
    {
        DB::transaction(function () use ($exam, $typeValue, $ids) {
            // allow only IDs that really belong to this type
            $desired = MedicalTest::query()
                ->where('type', $typeValue)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();

            // read current attached IDs for THIS type via the scoped relation
            $current = $exam
                ->medicalTests()
                ->where('medical_tests.type', $typeValue)
                ->pluck('medical_tests.id')
                ->all();

            $desired = array_values(array_unique(array_map('intval', $desired)));
            $current = array_values(array_unique(array_map('intval', $current)));

            $toAttach = array_diff($desired, $current);
            $toDetach = array_diff($current, $desired);

            if ($toDetach) {
                // detach ONLY these IDs (other types remain intact)
                $exam->medicalTests()->detach($toDetach);
            }
            if ($toAttach) {
                $exam->medicalTests()->attach($toAttach);
            }
        });
    }

    public function updateMedicalTests(): void
    {
        $this->medicalExamination->load('medicalTests');
        $this->addedMedicalTests = $this->medicalExamination->medicalTests->where(
            'type',
            $this->type,
        );

        // Load pivot media for each test
        $this->addedMedicalTests->each(function ($test) {
            if ($test->pivot) {
                $test->pivot->load('media');
            }
        });
    }

    /**
     * @throws Throwable
     */
    /*#[On('radiologyTestsUpdated')]
    public function radiologyTestsUpdated($value): void
    {
        $this->addedMedicalTests[] = $value;
        if (empty($value)) {
            $this->syncTestsForType(
                $this->medicalExamination,
                MedicalTestTypeEnum::RADIOLOGY_TESTS->value,
                [],  // array of IDs the user selected for LAB
            );
        } else {
            $this->syncTestsForType(
                $this->medicalExamination,
                MedicalTestTypeEnum::RADIOLOGY_TESTS->value,
                $value,  // array of IDs the user selected for LAB
            );
        }
        $this->updateMedicalTests();
        $this->dispatch('initSelect2');
        $this->dispatch('reRenderSelect2');
    }*/
    public function mount(MedicalExamination $medicalExamination): void
    {
        $this->medicalExamination = $medicalExamination;
        if ($this->type == MedicalTestTypeEnum::LABORATORY_TESTS) {
            $medicalTests = MedicalTest::getLaboratorySelect2();
        } else {
            $medicalTests = MedicalTest::getRadiologySelect2();
        }
        $addedMedicalTests = $this->medicalExamination->medicalTests->where(
            'type',
            $this->type,
        );

        // Load pivot media for each test
        $addedMedicalTests->each(function ($test) {
            if ($test->pivot) {
                $test->pivot->load('media');
            }
        });

        $this->medicalTests = $medicalTests;
        $this->addedMedicalTests = $addedMedicalTests;
        $this->listMedicalTests = array_map('strval', $addedMedicalTests->pluck('id')->toArray());
        $this->componentName = 'MedicalTestsLivewire'.$this->type->label();
    }

    public function render(): Factory|View
    {
        return view('doctor::livewire.medical-tests-livewire');
    }
}
