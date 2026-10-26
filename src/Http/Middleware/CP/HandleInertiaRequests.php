<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Inertia\Support\Header;
use Statamic\CP\Toasts\Manager;
use Statamic\Statamic;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Statamic\trans as __;

class HandleInertiaRequests extends Middleware
{
    public const ROOT_VIEW = 'statamic::layout';

    protected $rootView = self::ROOT_VIEW;

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function __construct(private Manager $toasts)
    {
        //
    }

    public function share(Request $request): array
    {
        return array_filter([
            ...parent::share($request),
            '_statamic' => [
                'cmsName' => __(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic'),
                'logos' => $this->logos(),
                'isCpRoute' => Statamic::isCpRoute(),
            ],
            '_toasts' => $this->toasts($request),
        ]);
    }

    public function handle(Request $request, Closure $next)
    {
        return parent::handle($request, function ($request) use ($next) {
            $response = $next($request);

            if ($request->header(Header::INERTIA) && $this->isDownload($response)) {
                return Inertia::location($request->fullUrl());
            }

            return $response;
        });
    }

    private function isDownload(Response $response): bool
    {
        return $response instanceof StreamedResponse || $response instanceof BinaryFileResponse;
    }

    private function logos()
    {
        if (! Statamic::pro()) {
            return [
                'text' => null,
                'siteName' => config('app.name'),
                'light' => ['nav' => null, 'outside' => null],
                'dark' => ['nav' => null, 'outside' => null],
            ];
        }

        if (is_string($light = config('statamic.cp.custom_logo_url'))) {
            $light = ['nav' => $light, 'outside' => $light];
        }

        if (is_string($dark = config('statamic.cp.custom_dark_logo_url'))) {
            $dark = ['nav' => $dark, 'outside' => $dark];
        }

        return [
            'text' => config('statamic.cp.custom_logo_text'),
            'siteName' => config('app.name'),
            'light' => [
                'nav' => $light['nav'] ?? null,
                'outside' => $light['outside'] ?? null,
            ],
            'dark' => [
                'nav' => $dark['nav'] ?? null,
                'outside' => $dark['outside'] ?? null,
            ],
        ];
    }

    private function toasts(Request $request)
    {
        $session = $request->session();

        if ($message = $session->get('success')) {
            $this->toasts->success($message);
        }

        // Laravel's built-in auth flows (password reset, etc.) flash to 'status'.
        if ($message = $session->get('status')) {
            $this->toasts->success($message);
        }

        if ($message = $session->get('error')) {
            $this->toasts->error($message);
        }

        if ($message = $session->get('info')) {
            $this->toasts->info($message);
        }

        $toasts = $this->toasts->toArray();

        $this->toasts->clear();

        return $toasts;
    }
}
