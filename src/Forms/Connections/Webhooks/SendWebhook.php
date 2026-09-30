<?php

namespace Statamic\Forms\Connections\Webhooks;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Statamic\Contracts\Forms\Submission;
use Statamic\Imaging\RemoteUrlValidator;
use Statamic\Sites\Site;

class SendWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    public function __construct(public Submission $submission, public Site $site, public array $config)
    {
    }

    public function backoff(): array
    {
        return [10, 30, 120, 600];
    }

    public function handle(): void
    {
        $submission = $this->submission->form()->submission($this->submission->id()) ?? $this->submission;

        $url = $this->config['url'] ?? null;
        $scheme = strtolower((string) parse_url((string) $url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'])) {
            throw new InvalidArgumentException("Webhook URL [{$url}] must use the http or https scheme.");
        }

        $response = Http::asJson()
            ->connectTimeout(5)
            ->timeout(30)
            ->withoutRedirecting()
            ->when(($this->config['verify_ssl'] ?? true) === false, fn ($http) => $http->withoutVerifying())
            ->unless(app()->environment('local'), fn ($http) => $http->withOptions($this->pinnedOptions($url)))
            ->post($url, [
                'form' => $submission->form()->handle(),
                'submission' => $submission->toArray(),
            ]);

        if ($response->redirect()) {
            throw new RequestException($response);
        }

        $response->throw();
    }

    public static function resolve(string $url): array
    {
        // Credentials don't affect where the request goes, and are a common way to protect webhook endpoints.
        return app(RemoteUrlValidator::class)->resolve(preg_replace('#^([a-z][a-z0-9+.-]*://)[^/?\#]*@#i', '$1', $url));
    }

    protected function supportsConnectionPinning(): bool
    {
        return extension_loaded('curl');
    }

    private function pinnedOptions(string $url): array
    {
        // Without curl there's no way to pin the connection, so the host could be rebound to an internal address.
        if (! $this->supportsConnectionPinning()) {
            throw new RuntimeException('The curl PHP extension is required to send webhooks.');
        }

        $resolved = static::resolve($url);

        return [
            'curl' => [
                CURLOPT_RESOLVE => [sprintf('%s:%d:%s', $resolved['host'], $resolved['port'], implode(',', $resolved['ips']))],
            ],
        ];
    }
}
