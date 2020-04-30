<?php

namespace Statamic\Auth\Protect;

use InvalidArgumentException;
use Statamic\Facades\URL;

class Protection
{
    protected $data;
    protected $manager;

    public function __construct(ProtectorManager $manager)
    {
        $this->manager = $manager;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function data()
    {
        return $this->data;
    }

    public function scheme()
    {
        return config('statamic.protect.default') ?? optional($this->data)->get('protect');
    }

    public function driver()
    {
        if (! $scheme = $this->scheme()) {
            // No scheme defined, nothing should happen.
            return $this->manager->driver('null');
        }

        try {
            return $this->manager->driver($scheme);
        } catch (InvalidArgumentException $e) {
            $this->log($e->getMessage());

            return $this->manager->createFallbackDriver();
        }
    }

    public function protect()
    {
        $this->driver()
            ->setUrl($this->url())
            ->setData($this->data())
            ->protect();
    }

    protected function url()
    {
        return URL::tidy(request()->url());
    }

    protected function log($message)
    {
        \Log::debug(vsprintf('%s Denying access to %s.', [
            $message,
            $this->url(),
        ]));
    }
}
