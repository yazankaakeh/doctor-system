<?php

namespace Modules\Doctor\Http\Controllers;

use App\Enum\Pagination;
use App\Http\Controllers\Controller;
use Modules\Doctor\Http\Requests\MedicalTestRequest;
use Modules\Doctor\Models\MedicalTest;

class MedicalTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = MedicalTest::query()->paginate(Pagination::PAG->value);

        return view('doctor::doctor.medicalTest.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedicalTestRequest $request)
    {
        MedicalTest::query()->create($request->validated());

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedicalTestRequest $request)
    {
        MedicalTest::query()->where('id', $request->id)->update($request->validated());

        return redirect()->back();
    }
}
