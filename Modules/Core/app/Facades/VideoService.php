<?php

namespace Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Core\Contracts\VideoServiceInterface;
use Modules\Core\DataTransferObjects\MeetingParticipant;
use Modules\Core\DataTransferObjects\MeetingRoom;
use Modules\Core\Services\Video\VideoServiceManager;

/**
 * @method static MeetingRoom createRoom(string $identifier, array $options = [])
 * @method static string getRoomUrl(string $roomName, MeetingParticipant $participant)
 * @method static string|null generateToken(string $roomName, MeetingParticipant $participant, int $expiresInMinutes = 60)
 * @method static bool isRoomActive(string $roomName)
 * @method static array getEmbedConfig(string $roomName, MeetingParticipant $participant)
 * @method static string getProviderName()
 * @method static string getDomain()
 * @method static VideoServiceInterface driver(?string $driver = null)
 * @method static VideoServiceManager extend(string $driver, callable $callback)
 * @method static array getAvailableDrivers()
 *
 * @see VideoServiceManager
 */
class VideoService extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'video.service';
    }
}
