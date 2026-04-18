<?php

namespace Modules\Doctor\Actions\Profile;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Modules\Doctor\Models\Doctor;

class UpdateProfileAction
{
    /**
     * Handle updating the doctor's profile.
     */
    public function handle(Doctor $doctor, array $data, ?UploadedFile $avatar = null): Doctor
    {
        // Handle password update
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Remove avatar from data as it's handled separately
        unset($data['avatar']);

        // Update doctor data
        $doctor->update($data);

        // Handle avatar upload
        if ($avatar) {
            $doctor->clearMediaCollection('img');
            $doctor->addMedia($avatar)->toMediaCollection('img');
        }

        return $doctor;
    }
}
