<?php

namespace Modules\Doctor\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Doctor\Actions\ReportAction;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctorId = auth()->user()->id;
        $data = ReportAction::getReports($doctorId);

        return view('doctor::dashboard', compact('data'));
    }
}
