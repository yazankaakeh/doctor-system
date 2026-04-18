<?php

namespace Modules\Doctor\Http\Controllers;

use App\Enum\Pagination;
use App\Http\Controllers\Controller;
use Modules\Doctor\Http\Requests\MedicalSpecialtyRequest;
use Modules\Doctor\Models\MedicalSpecialty;

class MedicalSpecialtyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = MedicalSpecialty::query()->paginate(Pagination::PAG->value);

        return view('doctor::doctor.medicalSpecialty.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedicalSpecialtyRequest $request)
    {
        MedicalSpecialty::query()->create($request->validated());

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedicalSpecialtyRequest $request)
    {
        MedicalSpecialty::query()->where('id', $request->id)
            ->update($request->validated());

        return redirect()->back();
    }
}
