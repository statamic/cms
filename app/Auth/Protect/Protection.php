<?php

namespace Statamic\Auth\Protect;

use Statamic\API\URL;
use InvalidArgumentException;
use Statamic\Auth\Protect\Protectors\NullProtector;
use Statamic\Auth\Protect\Protectors\FallbackProtector;

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

        if (! $config = config("statamic.protect.schemes.{$scheme}")) {
            $this->log("Invalid protection scheme [$scheme].");
            return $this->manager->driver('fallback');
        }

        if (! $driver = $config['driver'] ?? null) {
            $this->log("No driver provided in protection scheme [$scheme].");
            return $this->manager->driver('fallback');
        }

        try {
            return $this->manager->driver($driver);
        } catch (InvalidArgumentException $e) {
            $this->log("Invalid driver [$driver] in protection scheme [$scheme].");
            return $this->manager->driver('fallback');
        }
    }

    public function protect()
    {
        $this->driver()
            ->setUrl($this->url())
            ->setData($this->data())
            ->setScheme($this->scheme())
            ->setConfig(config("statamic.protect.schemes.{$this->scheme()}"))
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
            $this->url()
        ]));
    }
}
