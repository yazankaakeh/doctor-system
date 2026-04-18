<?php

namespace Modules\Patient\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Modules\Core\app\Models\Country;
use Modules\Patient\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    /**
     * Display patient profile.
     */
    public function index(): View
    {
        $patient = auth('web')->user();
        $countries = Country::query()->get();

        return view('patient::profile.index', compact('patient', 'countries'));
    }

    /**
     * Update patient profile.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $patient = auth('web')->user();

        $data = $request->validated();

        // Handle password update
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $patient->update($data);

        return redirect()->route('patient.profile.index')
            ->with('success', trans('patient::patient.profile_updated'));
    }
}
