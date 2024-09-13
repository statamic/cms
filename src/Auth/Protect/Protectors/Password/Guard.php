<?php

namespace Statamic\Auth\Protect\Protectors\Password;

class Guard
{
    public function __construct(protected $validPasswords)
    {
    }

    public function check($password)
    {
        return in_array($password, $this->validPasswords);
    }
}
