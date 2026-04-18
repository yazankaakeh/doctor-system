<?php

namespace Modules\Doctor\Http\Controllers;

use App\Enum\Pagination;
use App\Http\Controllers\Controller;
use Modules\Doctor\Http\Requests\MedicineRequest;
use Modules\Doctor\Models\Medicine;

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Medicine::query()->paginate(Pagination::PAG->value);

        return view('doctor::doctor.medicine.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedicineRequest $request)
    {
        Medicine::query()->create($request->validated());

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedicineRequest $request)
    {
        Medicine::query()->where('id', $request->id)->update($request->validated());

        return redirect()->back();
    }
}
