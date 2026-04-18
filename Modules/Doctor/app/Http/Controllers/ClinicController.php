<?php

namespace Modules\Doctor\Http\Controllers;

use App\Enum\Pagination;
use App\Http\Controllers\Controller;
use Modules\Doctor\Http\Requests\ClinicRequest;
use Modules\Doctor\Models\Clinic;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class ClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Clinic::query()->paginate(Pagination::PAG->value);

        return view('doctor::doctor.clinics.index', compact('data'));
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function store(ClinicRequest $request)
    {
        /** @var Clinic $clinic */
        $clinic = Clinic::query()->create([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);
        if ($request->file('img')) {
            $clinic->addMedia($request->file('img'))->toMediaCollection('images');
        }

        return redirect()->route('doctor.clinic.index');
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(ClinicRequest $request)
    {
        /** @var Clinic $clinic */
        $clinic = Clinic::query()->findOrFail($request->id);

        /** @var Clinic $clinic */
        $clinic->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);
        if ($request->file('img')) {
            $clinic
                ->addMedia($request->file('img'))
                ->toMediaCollection('images');
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
