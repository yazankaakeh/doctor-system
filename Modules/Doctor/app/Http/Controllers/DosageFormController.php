<?php

namespace Modules\Doctor\Http\Controllers;

use App\Enum\Pagination;
use App\Http\Controllers\Controller;
use Modules\Doctor\Http\Requests\DosageFormRequest;
use Modules\Doctor\Models\DosageForm;

class DosageFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = DosageForm::query()->paginate(Pagination::PAG->value);

        return view('doctor::doctor.dosageForm.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DosageFormRequest $request)
    {
        DosageForm::query()->create($request->validated());

        return redirect()->back()->with('success', 'added successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DosageFormRequest $request)
    {
        DosageForm::query()->where('id', $request->id)->update($request->validated());

        return redirect()->back()->with('success', 'added successfully');
    }
}
