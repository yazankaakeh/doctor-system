<?php

namespace Modules\Doctor\Http\Controllers;

use App\Enum\Pagination;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\app\Models\Country;
use Modules\Doctor\Actions\VCard;
use Modules\Doctor\Http\Requests\PatientRequest;
use Modules\Doctor\Models\Clinic;
use Modules\Doctor\Models\MedicalExamination;
use Modules\Doctor\Models\Patient;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'name',
            'age',
            'min_age',
            'max_age',
            'children',
            'nationality_id',
            'blood_type',
            'gender',
            'marital_status',
            'is_active',
        ]);
        $data = Patient::query()->filter($filters)->with(['media'])->paginate(Pagination::PAG->value);
        $countries = Country::getCountriesSelect2();
        $clinics = Clinic::getClinicSelect2();

        return view('doctor::doctor.patients.index', compact('data', 'countries', 'clinics'));
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function store(PatientRequest $request)
    {
        /** @var Patient $patient */
        $patient = Patient::query()->create($request->all());
        if ($request->file('img')) {
            $patient->addMedia($request->file('img'))->toMediaCollection('images');
        }
        $patient->clinics()->sync($request->clinics_id);
        $patient->clinics()->attach($request->clinics);

        return redirect()->back();
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $patient = Patient::query()
            ->where('id', $id)
            ->with('media')
            ->with('clinics')
            ->with('finalDiagnosis')
            ->first();
        $medicalExaminations = MedicalExamination::patientMedicalExaminationsWithoutId($id)->get();

        return view('doctor::doctor.patients.show', compact('patient', 'medicalExaminations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PatientRequest $request)
    {
        /** @var Patient $patient */
        $patient = Patient::query()->updateOrCreate(
            ['id' => $request->id],
            $request->validated(),
        );

        $patient->clinics()->sync($request->clinics_id);
        if ($request->file('img')) {
            $patient->addMedia($request->file('img'))->toMediaCollection('images');
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function downloadVCard($id)
    {
        $vcard = new VCard;
        $patient = Patient::query()->findOrFail($id);
        $content = $vcard->download($patient);
        $filename = $vcard->getFilename($patient->name);

        return response($content, 200, [
            'Content-Type' => 'text/vcard; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
