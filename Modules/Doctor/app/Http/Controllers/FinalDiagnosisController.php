<?php

namespace Modules\Doctor\Http\Controllers;

use App\Enum\Pagination;
use App\Http\Controllers\Controller;
use Modules\Doctor\Http\Requests\FinalDiagnosisRequest;
use Modules\Doctor\Models\FinalDiagnosis;

class FinalDiagnosisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = FinalDiagnosis::query()->paginate(Pagination::PAG->value);

        return view('doctor::doctor.finalDiagnosis.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FinalDiagnosisRequest $request)
    {
        FinalDiagnosis::query()->create($request->validated());

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FinalDiagnosisRequest $request)
    {
        FinalDiagnosis::query()->where('id', $request->id)->update($request->validated());

        return redirect()->back();
    }
}
