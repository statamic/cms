<?php

namespace Statamic\CP;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use JsonSerializable;
use Statamic\Statamic;
use Statamic\Support\Str;

class Breadcrumbs implements Arrayable, JsonSerializable
{
    protected $crumbs;

    public function __construct($crumbs)
    {
        $this->crumbs = collect($crumbs);
    }

    public static function make($crumbs)
    {
        return new static($crumbs);
    }

    public function toArray()
    {
        return $this->crumbs->toArray();
    }

    public function toJson()
    {
        return $this->crumbs->toJson();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->crumbs->toArray();
    }

    public function title($title = null)
    {
        $crumbs = $this->crumbs->map(fn ($v) => __($v['text']));

        if ($title) {
            $crumbs->push(__($title));
        }

        $arrow = Statamic::cpDirection() === 'ltr' ? ' ‹ ' : ' › ';

        return $crumbs->reverse()->join($arrow);
    }

    public static function addFiltersFromReferer(string $validRefererRoute, string $handle)
    {
        $referer = request()->headers->get('referer');
        if (! $referer) {
            return false;
        }

        $refererRequest = Request::create($referer);
        if (Route::getRoutes()->match($refererRequest)?->uri() !== $validRefererRoute) {
            return false;
        }

        if (! Str::contains($refererRequest->getPathInfo(), $handle)) {
            return false;
        }

        if (! $refererRequest->hasAny(['sort', 'order', 'filters', 'search', 'page'])) {
            return false;
        }

        return true;
    }
}
