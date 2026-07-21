<?php

namespace Statamic\Forms\Connections\Webhooks;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Statamic\Contracts\Forms\Submission;
use Statamic\Sites\Site;

class SendWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Submission $submission, public Site $site, public $config)
    {
    }

    public function handle()
    {
        $url = $this->config['url'] ?? null;
        $scheme = strtolower((string) parse_url((string) $url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'])) {
            throw new InvalidArgumentException("Webhook URL [{$url}] must use the http or https scheme.");
        }

        Http::asJson()
            ->connectTimeout(5)
            ->timeout(30)
            ->withOptions(['allow_redirects' => ['max' => 5, 'protocols' => ['http', 'https']]])
            ->when(($this->config['verify_ssl'] ?? true) === false, fn ($http) => $http->withoutVerifying())
            ->post($url, [
                'form' => $this->submission->form()->handle(),
                'submission' => $this->submission->toArray(),
            ])
            ->throw();
    }
}
