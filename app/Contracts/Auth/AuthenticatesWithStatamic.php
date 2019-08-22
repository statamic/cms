<?php

namespace Statamic\Contracts\Auth;

interface AuthenticatesWithStatamic
{
    public function statamicUser(): User;
}
