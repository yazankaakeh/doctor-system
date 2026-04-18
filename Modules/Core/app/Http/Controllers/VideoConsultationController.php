<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Actions\Video\CreateMeetingRoomAction;
use Modules\Core\Actions\Video\GetMeetingConfigAction;
use Modules\Core\DataTransferObjects\MeetingParticipant;
use Modules\Core\DataTransferObjects\ParticipantRole;

class VideoConsultationController extends Controller
{
    /**
     * Join a video consultation room.
     */
    public function join(
        Request $request,
        string $roomName,
        GetMeetingConfigAction $getMeetingConfig
    ): View {
        $user = $request->user();
        $guard = $this->detectGuard($request);

        // Create participant based on user type
        $participant = $this->createParticipant($user, $guard);

        // Get meeting configuration
        $config = $getMeetingConfig->handle($roomName, $participant);

        return view('core::video.consultation', [
            'roomName' => $roomName,
            'participant' => $participant,
            'config' => $config,
            'bookingId' => $request->query('booking'),
            'returnUrl' => $request->query('return', $this->getDefaultReturnUrl($guard)),
        ]);
    }

    /**
     * Create a new meeting room and redirect.
     */
    public function create(
        Request $request,
        CreateMeetingRoomAction $createRoom
    ) {
        $identifier = $request->input('identifier', uniqid('room_'));
        $options = $request->only(['expires_at']);

        $room = $createRoom->handle($identifier, $options);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'room' => $room->toArray(),
            ]);
        }

        return redirect()->route('video.join', [
            'roomName' => $room->name,
            'booking' => $request->input('booking_id'),
        ]);
    }

    /**
     * Get room information via API.
     */
    public function info(
        Request $request,
        string $roomName,
        GetMeetingConfigAction $getMeetingConfig
    ) {
        $user = $request->user();
        $guard = $this->detectGuard($request);
        $participant = $this->createParticipant($user, $guard);

        $config = $getMeetingConfig->handle($roomName, $participant);

        return response()->json([
            'success' => true,
            'room' => $config,
        ]);
    }

    /**
     * Detect the current authentication guard.
     *
     * Iterates over a preferred list of guards but skips any that aren't
     * defined in config/auth.php (otherwise auth($guard) throws
     * "Auth guard [...] is not defined.").
     */
    protected function detectGuard(Request $request): string
    {
        $preferred = ['doctor', 'patient', 'admin', 'web'];

        foreach ($preferred as $guard) {
            if (! config("auth.guards.{$guard}")) {
                continue; // Guard not configured in this app
            }

            if (auth($guard)->check()) {
                return $guard;
            }
        }

        return 'web';
    }

    /**
     * Create a participant from the authenticated user.
     */
    protected function createParticipant(mixed $user, string $guard): MeetingParticipant
    {
        $role = match ($guard) {
            'doctor' => ParticipantRole::MODERATOR,
            default => ParticipantRole::PARTICIPANT,
        };

        $name = match ($guard) {
            'doctor' => 'Dr. ' . $user->name,
            default => $user->name,
        };

        $avatar = null;
        if (method_exists($user, 'getAvatarAttribute')) {
            $avatar = $user->avatar;
        } elseif (isset($user->avatar)) {
            $avatar = $user->avatar;
        }

        return new MeetingParticipant(
            id: (string) $user->id,
            name: $name,
            email: $user->email,
            role: $role,
            avatar: $avatar,
            metadata: [
                'type' => $guard,
                'user_id' => $user->id,
            ],
        );
    }

    /**
     * Get the default return URL based on guard.
     *
     * Falls back to the app root ('/') when a named dashboard route
     * isn't registered, so we never throw RouteNotFoundException here.
     */
    protected function getDefaultReturnUrl(string $guard): string
    {
        $candidates = match ($guard) {
            'doctor'  => ['doctor.dashboard', 'doctor.home', 'doctor.bookings.index'],
            'patient' => ['patient.dashboard', 'patient.home', 'patient.bookings.index'],
            'admin'   => ['admin.dashboard', 'admin.home'],
            default   => ['home', 'dashboard'],
        };

        foreach ($candidates as $name) {
            if (\Illuminate\Support\Facades\Route::has($name)) {
                return route($name);
            }
        }

        return url('/');
    }
}
