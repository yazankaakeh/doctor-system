<?php

namespace Modules\Core\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Modules\Core\Action\EnvUpdateClass;
use Modules\Core\App\Emails\TestMail;
use Modules\Core\App\Http\Requests\EnvUpdateRequest;

class EnvController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('core::env.updateEnv');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EnvUpdateRequest $request): RedirectResponse
    {
        try {
            // Handle Firebase service account file upload
            if ($request->hasFile('FIREBASE_SERVICE_ACCOUNT_FILE')) {
                $file = $request->file('FIREBASE_SERVICE_ACCOUNT_FILE');

                // Validate file type
                if ($file->getClientOriginalExtension() !== 'json') {
                    return redirect()->back()->with('error', 'Firebase service account file must be a JSON file.');
                }

                // Validate JSON content
                $content = file_get_contents($file->getPathname());
                $json = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return redirect()->back()->with('error', 'Invalid JSON file for Firebase service account.');
                }

                // Check for required Firebase service account fields
                $requiredFields = ['type', 'project_id', 'private_key', 'client_email'];
                foreach ($requiredFields as $field) {
                    if (! isset($json[$field])) {
                        return redirect()->back()->with('error', "Missing required field '{$field}' in Firebase service account file.");
                    }
                }

                // Store the file
                $file->move(storage_path(), 'firebase-service-account.json');
            }

            EnvUpdateClass::updateEnvSettings([
                // recaptcha
                'RECAPTCHA_SITE_KEY' => $request->RECAPTCHA_SITE_KEY,
                'RECAPTCHA_SECRET_KEY' => $request->RECAPTCHA_SECRET_KEY,
                'RECAPTCHA_LINK' => $request->RECAPTCHA_LINK,

                // Email
                'MAIL_MAILER' => $request->MAIL_MAILER,
                'MAIL_HOST' => $request->MAIL_HOST,
                'MAIL_PORT' => $request->MAIL_PORT,
                'MAIL_USERNAME' => $request->MAIL_USERNAME,
                'MAIL_PASSWORD' => $request->MAIL_PASSWORD,
                'MAIL_ENCRYPTION' => $request->MAIL_ENCRYPTION,
                'MAIL_FROM_ADDRESS' => $request->MAIL_FROM_ADDRESS,
                'MAIL_FROM_NAME' => $request->MAIL_FROM_NAME,

                // Firebase
                'FIREBASE_API_KEY' => $request->FIREBASE_API_KEY,
                'FIREBASE_AUTH_DOMAIN' => $request->FIREBASE_AUTH_DOMAIN,
                'FIREBASE_PROJECT_ID' => $request->FIREBASE_PROJECT_ID,
                'FIREBASE_STORAGE_BUCKET' => $request->FIREBASE_STORAGE_BUCKET,
                'FIREBASE_MESSAGING_SENDER_ID' => $request->FIREBASE_MESSAGING_SENDER_ID,
                'FIREBASE_APP_ID' => $request->FIREBASE_APP_ID,
            ]);

            return redirect()->back()->with('success', 'Environment settings updated successfully.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update environment settings: '.$e->getMessage());
        }
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            Mail::to($request->email)->send(new TestMail);

            return back()->with('success', 'Test email sent successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed: '.$e->getMessage());
        }
    }

    /**
     * Save Firebase push token for the authenticated user
     */
    public function savePushToken(Request $request)
    {
        $request->validate([
            'push_token' => 'required|string',
            'platform' => 'required|string|in:web,android,ios',
        ]);

        try {
            $user = auth()->user();

            // Check if token already exists for this user and platform
            $existingToken = \Modules\Notification\Models\NotificationPushToken::where('tokenable_id', $user->id)
                ->where('tokenable_type', get_class($user))
                ->where('platform', $request->platform)
                ->first();

            if ($existingToken) {
                // Update existing token
                $existingToken->update(['push_token' => $request->push_token]);
            } else {
                // Create new token
                \Modules\Notification\Models\NotificationPushToken::create([
                    'tokenable_id' => $user->id,
                    'tokenable_type' => get_class($user),
                    'push_token' => $request->push_token,
                    'platform' => $request->platform,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Push token saved successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save push token: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send test Firebase notification
     */
    public function sendTestNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ]);

        try {
            $user = auth()->user();

            // Check if user has push tokens
            $pushTokens = $user->pushTokens;
            if ($pushTokens->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No push tokens found. Please save a push token first.',
                ], 400);
            }

            // Send notification using Firebase service
            $firebaseService = new \Modules\Notification\App\Services\Notifications\FireBase;

            $notificationData = [
                'title' => $request->title,
                'body' => $request->body,
            ];

            $firebaseService->prepareAndSend(
                $user,
                $notificationData,
                true, // vibrate
                'default', // sound
                ['url' => route('doctor.dashboard')] // click action
            );

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully to '.$pushTokens->count().' device(s).',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: '.$e->getMessage(),
            ], 500);
        }
    }
}
