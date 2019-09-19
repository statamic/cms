<?php

namespace Statamic\Auth\Protect\Protectors\Password;

class Guard
{
    protected $config;

    public function __construct($scheme)
    {
        $this->config = config("statamic.protect.schemes.$scheme");
    }

    public function check($password)
    {
        $allowed = array_get($this->config, 'allowed', []);

        return in_array($password, $allowed);
    }
}
