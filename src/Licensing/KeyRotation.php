<?php

namespace Statamic\Licensing;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class KeyRotation
{
    public const URL = 'https://statamic.com/licensing/rotate';

    public function rotate(string $oldKey, string $newKey): void
    {
        $response = Http::acceptJson()->asJson()->timeout(10)->post($this->url(), [
            'old_key' => $oldKey,
            'new_key' => $newKey,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to rotate the site key on statamic.com.');
        }
    }

    public function url(): string
    {
        return rtrim(config('statamic.system.licensing_url', 'https://statamic.com'), '/').'/licensing/rotate';
    }
}
