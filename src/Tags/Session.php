<?php

namespace Statamic\Tags;

class Session extends Tags
{
    public function index()
    {
        return session()->all();
    }

    public function dump()
    {
        dump(session()->all());
    }

    public function set()
    {
        foreach ($this->params as $key => $value) {
            session()->put($key, $value);
        }

        return session()->all();
    }

    public function flash()
    {
        foreach ($this->params as $key => $value) {
            session()->flash($key, $value);
        }

        return session()->all();
    }

    public function flush()
    {
        session()->flush();
    }

    public function forget()
    {
        foreach ($this->params as $key => $value) {
            session()->forget($key);
        }

        return session()->all();
    }
}
