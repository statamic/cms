<?php

namespace Statamic\Licensing;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class DeviceFlow
{
    public const START_URL = 'https://statamic.com/licensing/device';

    public const POLL_URL = 'https://statamic.com/licensing/device/poll';

    /**
     * @return array{url: string, code: string, device_code: string, interval: int}
     */
    public function start(string $key, string $host): array
    {
        $response = Http::acceptJson()->asJson()->timeout(10)->post($this->startUrl(), [
            'key' => $key,
            'host' => $host,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to start the licensing flow.');
        }

        return [
            'url' => $response->json('url'),
            'code' => $response->json('code'),
            'device_code' => $response->json('device_code'),
            'interval' => (int) ($response->json('interval') ?: 5),
        ];
    }

    /**
     * @return array{status: string}
     */
    public function poll(string $deviceCode): array
    {
        $response = Http::acceptJson()->asJson()->timeout(10)->post($this->pollUrl(), [
            'device_code' => $deviceCode,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to check licensing status.');
        }

        return [
            'status' => $response->json('status', 'pending'),
        ];
    }

    public function startUrl(): string
    {
        return rtrim(config('statamic.system.licensing_url', 'https://statamic.com'), '/').'/licensing/device';
    }

    public function pollUrl(): string
    {
        return $this->startUrl().'/poll';
    }
}
