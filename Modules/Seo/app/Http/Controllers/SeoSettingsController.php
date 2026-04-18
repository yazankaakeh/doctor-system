<?php

namespace Modules\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Seo\Http\Requests\SeoSettingsRequest;
use Modules\Seo\Models\SeoSettings;

class SeoSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = SeoSettings::query()->get();

        return view('seo::index', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SeoSettingsRequest $request): RedirectResponse
    {
        try {
            // Get validated data from request
            $validatedData = $request->validated();

            // Filter out null/empty values to avoid unnecessary updates
            $settingsToUpdate = array_filter($validatedData, function ($value) {
                return ! is_null($value) && $value !== '';
            });

            // Update settings using the model helper method
            $updatedSettings = SeoSettings::updateSettings($settingsToUpdate);

            // Check if any settings were actually updated
            if (empty($updatedSettings)) {
                return redirect()->back()->with('warning', 'No valid settings were provided for update.');
            }

            return redirect()->back()->with('success', 'SEO settings updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update SEO settings: '.$e->getMessage());
        }
    }
}
