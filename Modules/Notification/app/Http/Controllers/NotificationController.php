<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Modules\Notification\App\Actions\PushNotificationAction;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('notification::index');
    }

    public function sendTestEmail(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        $details = [
            'subject' => 'Test Email from Mavera',
            'body' => 'This is a test email sent.',
        ];

        Mail::raw($details['body'], function ($message) use ($details, $email) {
            $message->to($email)  // replace with the recipient's email address
                ->subject($details['subject']);
        });

        return redirect()->back();

    }

    public function push_notification(Request $request): JsonResponse
    {
        $user = Auth::user();
        Log::error('asd asd asd');
        Log::log('info', "in Controller  token: {$request['push_token']}  platform: {$request['platform']}  customer_id: {$user->id}");
        $data = PushNotificationAction::action($user, $request->all());

        return response()->json([$data], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NotificationsRequest $request): RedirectResponse
    {
        UpdateNotificationsEnv::update($request->validated());
        session()->flash('success', trans('cms::app.configEnv.successMSG'));

        return redirect()->back();
    }
}
