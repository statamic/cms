<?php

namespace Statamic\Tags;

class Session extends Tags
{
    public function wildcard($tag)
    {
        $key = $this->context->value($tag, $tag);

        $key = str_replace(':', '.', $key);

        return session()->get($key, $this->params->get('default'));
    }

    public function index()
    {
        return $this->returnableSession();
    }

    public function dump()
    {
        dump(session()->all());
    }

    public function value()
    {
        $key = $this->params->get('key');

        $key = str_replace(':', '.', $key);

        return session()->get($key, $this->params->get('default'));
    }

    public function set()
    {
        foreach ($this->params as $key => $value) {
            session()->put($key, $value);
        }

        return $this->returnableSession();
    }

    public function flash()
    {
        foreach ($this->params as $key => $value) {
            session()->flash($key, $value);
        }

        return $this->returnableSession();
    }

    public function flush()
    {
        session()->flush();
    }

    public function forget()
    {
        foreach ($this->params->explode('keys') as $key) {
            session()->forget($key);
        }

        return $this->returnableSession();
    }

    public function has()
    {
        return session()->has($this->params->get('key'));
    }

    protected function returnableSession()
    {
        if (! $this->isPair) {
            return;
        }

        if ($as = $this->params->get('as')) {
            return [$as => session()->all()];
        }

        return session()->all();
    }
}
