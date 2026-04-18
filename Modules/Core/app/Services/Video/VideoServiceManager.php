<?php

namespace Modules\Core\Services\Video;

use InvalidArgumentException;
use Modules\Core\Contracts\VideoServiceInterface;

class VideoServiceManager
{
    /**
     * The array of resolved video services.
     */
    protected array $services = [];

    /**
     * The registered custom service creators.
     */
    protected array $customCreators = [];

    /**
     * Get a video service instance.
     */
    public function driver(?string $driver = null): VideoServiceInterface
    {
        $driver = $driver ?? $this->getDefaultDriver();

        return $this->services[$driver] ?? $this->services[$driver] = $this->resolve($driver);
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return config('core.video.default', 'jitsi');
    }

    /**
     * Resolve the given video service.
     */
    protected function resolve(string $driver): VideoServiceInterface
    {
        // Check for custom creator first
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        }

        $method = 'create' . ucfirst($driver) . 'Driver';

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        throw new InvalidArgumentException("Video driver [{$driver}] is not supported.");
    }

    /**
     * Call a custom driver creator.
     */
    protected function callCustomCreator(string $driver): VideoServiceInterface
    {
        return $this->customCreators[$driver]($this->getConfig($driver));
    }

    /**
     * Register a custom driver creator.
     */
    public function extend(string $driver, callable $callback): self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Create the Jitsi driver.
     */
    protected function createJitsiDriver(): VideoServiceInterface
    {
        return new JitsiVideoService();
    }

    /**
     * Get the configuration for a driver.
     */
    protected function getConfig(string $driver): array
    {
        return config("core.video.providers.{$driver}", []);
    }

    /**
     * Get all available drivers.
     */
    public function getAvailableDrivers(): array
    {
        return array_keys(config('core.video.providers', []));
    }

    /**
     * Dynamically call the default driver instance.
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->driver()->$method(...$parameters);
    }
}
