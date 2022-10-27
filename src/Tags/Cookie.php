<?php

namespace Statamic\Tags;

use Illuminate\Support\Facades\Cookie as CookieFacade;

class Cookie extends Tags
{
    public function wildcard($tag)
    {
        $key = $this->context->value($tag, $tag);

        $key = str_replace(':', '.', $key);

        return CookieFacade::get($key, $this->params->get('default'));
    }

    public function index()
    {
        return $this->returnableCookie();
    }

    public function value()
    {
        $key = $this->params->get('key');

        $key = str_replace(':', '.', $key);

        return CookieFacade::get($key, $this->params->get('default'));
    }

    public function set()
    {
        $params = $this->params->except(['minutes']);
        foreach ($params as $key => $value) {
            CookieFacade::queue(CookieFacade::make($key, $value, $this->params->get('minutes', 60)));
        }

        return $this->returnableCookie();
    }

    public function forget()
    {
        foreach ($this->params->explode('keys') as $key) {
            CookieFacade::queue(CookieFacade::forget($key));
        }

        return $this->returnableCookie();
    }

    public function has()
    {
        return CookieFacade::has($this->params->get('key'));
    }

    protected function returnableCookie()
    {
        if (! $this->isPair) {
            return;
        }

        if ($as = $this->params->get('as')) {
            return [$as => CookieFacade::get()];
        }

        return CookieFacade::get();
    }
}
