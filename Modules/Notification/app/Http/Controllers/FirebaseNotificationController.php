<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Notification\App\Actions\PushNotificationAction;

class FirebaseNotificationController extends Controller
{
    public function push_notification(Request $request): JsonResponse
    {
        $user = Auth::user();
        Log::error('asd asd asd');
        Log::log(
            'info',
            "in Controller  token: {$request['push_token']}  platform: {$request['platform']}  customer_id: {$user->id}",
        );
        $data = PushNotificationAction::action($user, $request->all());

        return response()->json([$data]);
    }
}
