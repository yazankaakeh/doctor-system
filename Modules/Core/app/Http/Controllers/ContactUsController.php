<?php

namespace Modules\Core\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\app\Http\Requests\ContactUsRequest;
use Modules\Core\app\Models\ContactUs;

class ContactUsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function submitContactForm(ContactUsRequest $request)
    {
        ContactUs::query()->create($request->all());

        return redirect()->back();
    }
}
