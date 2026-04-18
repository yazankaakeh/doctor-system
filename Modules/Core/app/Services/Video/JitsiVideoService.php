<?php

namespace Modules\Core\Services\Video;

use Firebase\JWT\JWT;
use Illuminate\Support\Str;
use Modules\Core\Contracts\VideoServiceInterface;
use Modules\Core\DataTransferObjects\MeetingParticipant;
use Modules\Core\DataTransferObjects\MeetingRoom;

class JitsiVideoService implements VideoServiceInterface
{
    protected string $domain;
    protected ?string $appId;
    protected ?string $secret;
    protected bool $selfHosted;
    protected array $roomConfig;
    protected array $uiConfig;
    protected array $defaults;

    public function __construct()
    {
        $config = config('core.video.providers.jitsi', []);

        $this->domain = $config['domain'] ?? 'meet.jit.si';
        $this->appId = $config['app_id'] ?? null;
        $this->secret = $config['secret'] ?? null;
        $this->selfHosted = $config['self_hosted'] ?? false;
        $this->roomConfig = config('core.video.room', []);
        $this->uiConfig = config('core.video.ui', []);
        $this->defaults = config('core.video.defaults', []);
    }

    /**
     * Create a meeting room.
     */
    public function createRoom(string $identifier, array $options = []): MeetingRoom
    {
        $roomName = $this->generateRoomName($identifier);
        $password = $this->shouldUsePassword() ? $this->generatePassword() : null;

        $url = $this->buildRoomUrl($roomName);

        return new MeetingRoom(
            name: $roomName,
            url: $url,
            provider: $this->getProviderName(),
            password: $password,
            config: array_merge($this->getDefaultConfig(), $options),
            expiresAt: $options['expires_at'] ?? null,
        );
    }

    /**
     * Get the room URL for a participant.
     */
    public function getRoomUrl(string $roomName, MeetingParticipant $participant): string
    {
        $baseUrl = $this->buildRoomUrl($roomName);

        // Add participant info as URL parameters
        $params = [
            'userInfo.displayName' => $participant->name,
            'userInfo.email' => $participant->email,
        ];

        if ($participant->avatar) {
            $params['userInfo.avatarURL'] = $participant->avatar;
        }

        return $baseUrl . '#' . http_build_query($params);
    }

    /**
     * Generate JWT token for authenticated access.
     */
    public function generateToken(string $roomName, MeetingParticipant $participant, int $expiresInMinutes = 60): ?string
    {
        // JWT is only available for self-hosted or 8x8 JaaS
        if (!$this->appId || !$this->secret) {
            return null;
        }

        $now = time();
        $payload = [
            'iss' => $this->appId,
            'sub' => $this->domain,
            'aud' => $this->appId,
            'iat' => $now,
            'exp' => $now + ($expiresInMinutes * 60),
            'nbf' => $now,
            'room' => $roomName,
            'context' => [
                'user' => [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'email' => $participant->email,
                    'avatar' => $participant->avatar,
                    'moderator' => $participant->isModerator(),
                ],
                'features' => [
                    'livestreaming' => false,
                    'recording' => config('core.video.room.recording_enabled', false),
                    'transcription' => false,
                    'outbound-call' => false,
                ],
            ],
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    /**
     * Check if room is active.
     */
    public function isRoomActive(string $roomName): bool
    {
        // For public Jitsi, rooms are always available
        // For self-hosted, you could implement an API check
        return true;
    }

    /**
     * Get embed configuration.
     */
    public function getEmbedConfig(string $roomName, MeetingParticipant $participant): array
    {
        $hasAuthentication = !empty($this->appId) && !empty($this->secret);
        $token = $hasAuthentication ? $this->generateToken($roomName, $participant) : null;

        $config = [
            'domain' => $this->domain,
            'roomName' => $roomName,
            'userInfo' => [
                'displayName' => $participant->name,
                'email' => $participant->email,
                'avatarURL' => $participant->avatar,
            ],
            'configOverwrite' => $this->getConfigOverwrite($participant),
            'interfaceConfigOverwrite' => $this->getInterfaceConfigOverwrite(),
        ];

        // Only include JWT if we have authentication configured
        // Including jwt:null can trigger lobby mode on public Jitsi
        if ($token) {
            $config['jwt'] = $token;
        }

        return $config;
    }

    /**
     * Get provider name.
     */
    public function getProviderName(): string
    {
        return 'jitsi';
    }

    /**
     * Get domain.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Generate a unique room name.
     */
    protected function generateRoomName(string $identifier): string
    {
        $prefix = $this->roomConfig['prefix'] ?? 'MedConsult';
        $uniqueId = Str::random(8);

        // Sanitize identifier for URL safety
        $safeIdentifier = preg_replace('/[^a-zA-Z0-9]/', '', $identifier);

        return sprintf('%s_%s_%s', $prefix, $safeIdentifier, $uniqueId);
    }

    /**
     * Build the room URL.
     */
    protected function buildRoomUrl(string $roomName): string
    {
        $protocol = $this->selfHosted ? 'https' : 'https';
        return sprintf('%s://%s/%s', $protocol, $this->domain, $roomName);
    }

    /**
     * Check if password should be used.
     */
    protected function shouldUsePassword(): bool
    {
        return $this->roomConfig['password_protected'] ?? false;
    }

    /**
     * Generate a random password.
     */
    protected function generatePassword(): string
    {
        return Str::random(12);
    }

    /**
     * Get default room configuration.
     */
    protected function getDefaultConfig(): array
    {
        return [
            'startWithAudioMuted' => $this->defaults['start_with_audio_muted'] ?? false,
            'startWithVideoMuted' => $this->defaults['start_with_video_muted'] ?? false,
            'enableScreenSharing' => $this->defaults['enable_screen_sharing'] ?? true,
            'enableChat' => $this->defaults['enable_chat'] ?? true,
        ];
    }

    /**
     * Get Jitsi config overwrite.
     */
    protected function getConfigOverwrite(MeetingParticipant $participant): array
    {
        $hasAuthentication = !empty($this->appId) && !empty($this->secret);

        // Base configuration for all meetings
        $config = [
            'startWithAudioMuted' => $this->defaults['start_with_audio_muted'] ?? false,
            'startWithVideoMuted' => $this->defaults['start_with_video_muted'] ?? false,
            'disableDeepLinking' => config('core.video.security.disable_deep_linking', true),
            'enableClosePage' => true,
            'disableInviteFunctions' => true,
            'enableNoisyMicDetection' => true,
            'enableNoAudioDetection' => true,
            'requireDisplayName' => config('core.video.security.require_display_name', true),
            'enableWelcomePage' => false,
            'hideConferenceSubject' => false,
            'hideConferenceTimer' => false,
            'hideParticipantsStats' => true,
            'maxFullResolutionParticipants' => 2,
            'resolution' => 720,
            'constraints' => [
                'video' => [
                    'height' => ['ideal' => 720, 'max' => 720, 'min' => 180],
                    'width' => ['ideal' => 1280, 'max' => 1280, 'min' => 320],
                ],
            ],
        ];

        // For public Jitsi without JWT: DO NOT set any lobby/prejoin configs
        // Setting them to false can still trigger lobby logic
        if (!$hasAuthentication) {
            // Completely omit lobby-related configs for public Jitsi
            return $config;
        }

        // Only add JWT-specific features if authentication is configured
        $config['prejoinPageEnabled'] = config('core.video.security.enable_lobby', false);

        // Moderator-specific settings
        if ($participant->isModerator()) {
            $config['enableLobby'] = config('core.video.room.lobby_enabled', false);
        }

        return $config;
    }

    /**
     * Get Jitsi interface config overwrite.
     */
    protected function getInterfaceConfigOverwrite(): array
    {
        return [
            'TOOLBAR_BUTTONS' => $this->uiConfig['toolbar_buttons'] ?? [
                'microphone',
                'camera',
                'desktop',
                'fullscreen',
                'hangup',
                'chat',
                'settings',
                'videoquality',
                'tileview',
            ],
            'SHOW_JITSI_WATERMARK' => $this->uiConfig['show_watermark'] ?? false,
            'SHOW_WATERMARK_FOR_GUESTS' => false,
            'SHOW_BRAND_WATERMARK' => $this->uiConfig['show_brand_watermark'] ?? false,
            'SHOW_CHROME_EXTENSION_BANNER' => false,
            'MOBILE_APP_PROMO' => false,
            'HIDE_INVITE_MORE_HEADER' => true,
            'DISABLE_JOIN_LEAVE_NOTIFICATIONS' => false,
            'DISABLE_PRESENCE_STATUS' => false,
            'DISABLE_RINGING' => false,
            'ENABLE_DIAL_OUT' => false,
            'ENABLE_FEEDBACK_ANIMATION' => false,
            'FILM_STRIP_MAX_HEIGHT' => 120,
            'DEFAULT_BACKGROUND' => '#1a1a2e',
            'DEFAULT_REMOTE_DISPLAY_NAME' => 'Participant',
            'PROVIDER_NAME' => config('app.name', 'Medical Consultation'),
            'APP_NAME' => config('app.name', 'Medical Consultation'),
            'NATIVE_APP_NAME' => config('app.name', 'Medical Consultation'),
            'LANG_DETECTION' => true,
            'VIDEO_QUALITY_LABEL_DISABLED' => false,
            'CONNECTION_INDICATOR_AUTO_HIDE_ENABLED' => true,
            'CONNECTION_INDICATOR_AUTO_HIDE_TIMEOUT' => 5000,
            'SETTINGS_SECTIONS' => ['language', 'moderator', 'profile'],
            'VERTICAL_FILMSTRIP' => true,
            'CLOSE_PAGE_GUEST_HINT' => false,
            'SHOW_PROMOTIONAL_CLOSE_PAGE' => false,
            'TOOLBAR_ALWAYS_VISIBLE' => false,
            'TOOLBAR_TIMEOUT' => 4000,
        ];
    }
}
