<?php

namespace Statamic\Sidecar;

use Closure;
use Illuminate\Support\Collection as IlluminateCollection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Statamic\Contracts\Entries\Entry;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Collection;

/**
 * @experimental
 */
class Manager
{
    protected array $customCreators = [];

    protected array $packages = [];

    protected array $resolved = [];

    protected bool $booted = false;

    public function extend(string $driver, Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Register a Sidecar driver package as compatible with an SSG/composer package.
     *
     * Used by `php please sidecar:install` to detect installed packages and
     * offer the matching driver. Called from driver service providers.
     */
    public function pair(string $compatiblePackage, string $driverPackage): self
    {
        $this->packages[$compatiblePackage] = $driverPackage;

        return $this;
    }

    public function hasDriver(string $driver): bool
    {
        return isset($this->customCreators[$driver]);
    }

    public function registeredDrivers(): array
    {
        return array_keys($this->customCreators);
    }

    public function driver(string $collectionHandle): Driver
    {
        if (isset($this->resolved[$collectionHandle])) {
            return $this->resolved[$collectionHandle];
        }

        $config = $this->getConfig($collectionHandle);

        if (is_null($config)) {
            throw new InvalidArgumentException("Sidecar collection [{$collectionHandle}] is not defined.");
        }

        $driver = $config['driver'] ?? null;

        if (! $driver) {
            throw new InvalidArgumentException("Sidecar collection [{$collectionHandle}] is missing a driver.");
        }

        if (! isset($this->customCreators[$driver])) {
            throw new InvalidArgumentException("Sidecar driver [{$driver}] is not defined.");
        }

        return $this->resolved[$collectionHandle] = $this->customCreators[$driver](
            app(),
            $config,
            $collectionHandle
        );
    }

    public function collections(): IlluminateCollection
    {
        return collect(config('statamic.sidecar.collections', []));
    }

    public function handles(): IlluminateCollection
    {
        return $this->collections()->keys();
    }

    public function manages(string $collectionHandle): bool
    {
        return $this->collections()->has($collectionHandle);
    }

    public function packages(): IlluminateCollection
    {
        return collect($this->packages);
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        $this->collections()->each(function (array $config, string $handle) {
            try {
                $this->bootCollection($handle);
            } catch (InvalidArgumentException $e) {
                Log::warning('Sidecar: '.$e->getMessage());
            }
        });

        $this->registerEventListeners();
    }

    protected function bootCollection(string $handle): void
    {
        $driver = $this->driver($handle);

        // Start from an existing on-disk collection when present so persisted
        // cascade data (e.g. SEO Pro section defaults) survives re-registration.
        $collection = $driver->configure(
            Collection::findByHandle($handle) ?? Collection::make($handle)
        );

        if ($previewUrl = $this->getConfig($handle)['preview_url'] ?? null) {
            $collection->previewTargets([
                [
                    'label' => 'Site',
                    'format' => $previewUrl,
                    'refresh' => true,
                ],
            ]);
        }

        Collection::register($collection);

        $driver->afterBoot($collection);
    }

    protected function registerEventListeners(): void
    {
        if ($this->handles()->isEmpty()) {
            return;
        }

        Event::listen(EntrySaved::class, function (EntrySaved $event) {
            $this->relayAfterSave($event->entry);
        });

        Event::listen(EntryDeleted::class, function (EntryDeleted $event) {
            $this->relayAfterDelete($event->entry);
        });

    }

    protected function relayAfterSave(Entry $entry): void
    {
        $handle = $entry->collectionHandle();

        if (! $this->manages($handle)) {
            return;
        }

        $this->driver($handle)->afterSave($entry);
    }

    protected function relayAfterDelete(Entry $entry): void
    {
        $handle = $entry->collectionHandle();

        if (! $this->manages($handle)) {
            return;
        }

        $this->driver($handle)->afterDelete($entry);
    }

    protected function getConfig(string $name): ?array
    {
        $config = config("statamic.sidecar.collections.{$name}");

        return is_array($config) ? $config : null;
    }
}
