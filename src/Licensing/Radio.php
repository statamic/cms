<?php

namespace Statamic\Licensing;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Statamic\Facades\Glide;
use Statamic\Support\Str;
use Throwable;

class Radio
{
    const PING_CACHE_KEY = 'statamic.outpost.pinged';
    const PING_INTERVAL = 300; // seconds

    public function __construct(private Outpost $outpost)
    {
    }

    public function ping(): void
    {
        if ($this->recentlyPinged()) {
            return;
        }

        $this->markAsPinged();

        try {
            $this->outpost->radio();
        } catch (Throwable $e) {
            Log::debug('Error contacting Outpost: '.$e->getMessage());
        }
    }

    private function recentlyPinged(): bool
    {
        return $this->cache()->has(self::PING_CACHE_KEY);
    }

    private function markAsPinged(): void
    {
        $this->cache()->put(self::PING_CACHE_KEY, now()->timestamp, self::PING_INTERVAL);
    }

    private function cache(): Repository
    {
        try {
            return Cache::store('outpost');
        } catch (InvalidArgumentException $e) {
            return Cache::store();
        }
    }

    public function shouldPingRequest(Request $request): bool
    {
        if ($request->isLivePreview()) {
            return false;
        }

        return ! $this->isGlideRequest($request);
    }

    public function shouldPingDuringRequest(Request $request): bool
    {
        return $this->isCpRequest($request) && $this->shouldPingRequest($request);
    }

    public function shouldPingAfterResponse(Request $request): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        return ! $this->isCpRequest($request) && $this->shouldPingRequest($request);
    }

    public function shouldPingCommand(?string $command): bool
    {
        if (app()->runningUnitTests() || $this->runningInCi()) {
            return false;
        }

        return ! $this->isCommandIgnored($command);
    }

    public function isCommandIgnored(?string $command): bool
    {
        if (! $command) {
            return true;
        }

        if (in_array($command, $this->ignoredCommands(), true)) {
            return true;
        }

        foreach ($this->ignoredCommandPrefixes() as $prefix) {
            if ($command === $prefix || str_starts_with($command, $prefix.':')) {
                return true;
            }
        }

        return false;
    }

    private function isCpRequest(Request $request): bool
    {
        if (! config('statamic.cp.enabled')) {
            return false;
        }

        $cp = config('statamic.cp.route');
        $path = $request->path();

        return $path === $cp
            || Str::startsWith($path, Str::finish($cp, '/'));
    }

    private function isGlideRequest(Request $request): bool
    {
        $route = trim((string) Glide::route(), '/');

        if ($route === '') {
            return false;
        }

        return $request->is($route, $route.'/*', '*/'.$route, '*/'.$route.'/*');
    }

    private function runningInCi(): bool
    {
        return filter_var($_SERVER['CI'] ?? $_ENV['CI'] ?? getenv('CI'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return list<string>
     */
    private function ignoredCommandPrefixes(): array
    {
        return [
            'horizon',
            'nightwatch',
            'octane',
            'pail',
            'pulse',
            'queue',
            'reverb',
            'schedule',
        ];
    }

    /**
     * @return list<string>
     */
    private function ignoredCommands(): array
    {
        return [
            'completion',
            'docs',
            'dump-server',
            'help',
            'inspire',
            'list',
            'pest',
            'serve',
            'test',
            'tinker',
        ];
    }
}
