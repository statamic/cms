<?php

namespace Statamic\Data;

use Statamic\API\URL;
use Statamic\Contracts\Data\Content\UrlBuilder;

trait Routable
{
    protected $slug;

    public function slug($slug = null)
    {
        if (is_null($slug)) {
            return $this->slug;
        }

        $this->slug = $slug;

        return $this;
    }

    public function uri()
    {
        if ($structure = $this->structure()) {
            return $structure->entryUri($this);
        }

        if (! $route = $this->collection()->route()) {
            return null;
        }

        return app(UrlBuilder::class)->content($this)->build($route);
    }

    public function url()
    {
        return URL::makeRelative($this->absoluteUrl());
    }

    public function absoluteUrl()
    {
        return vsprintf('%s/%s', [
            rtrim($this->site()->absoluteUrl(), '/'),
            ltrim($this->uri(), '/')
        ]);
    }

    public function ampUrl()
    {
        return !$this->ampable() ? null : vsprintf('%s/%s/%s', [
            rtrim($this->site()->absoluteUrl(), '/'),
            config('statamic.amp.route'),
            ltrim($this->uri(), '/')
        ]);
    }

    public function routeData()
    {
        $data = array_merge($this->values(), [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'published' => $this->published(),
        ]);

        if ($this->hasDate()) {
            $data = array_merge($data, [
                'year' => $this->date()->format('Y'),
                'month' => $this->date()->format('m'),
                'day' => $this->date()->format('d'),
            ]);
        }

        return $data;
    }
}
