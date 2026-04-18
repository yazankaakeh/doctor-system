<?php

namespace Modules\Doctor\Http\Controllers;

use App\Enum\Pagination;
use App\Http\Controllers\Controller;
use Modules\Doctor\Http\Requests\VitalSignRequest;
use Modules\Doctor\Models\VitalSign;

class VitalSignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = VitalSign::query()->paginate(Pagination::PAG->value);

        return view('doctor::doctor.vitalSign.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VitalSignRequest $request)
    {
        VitalSign::query()->create($request->validated());

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VitalSignRequest $request)
    {
        VitalSign::query()->where('id', $request->id)->update($request->validated());

        return redirect()->back();
    }
}
