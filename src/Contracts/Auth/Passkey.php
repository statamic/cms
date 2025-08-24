<?php

namespace Statamic\Contracts\Auth;

interface Passkey
{
    public function id($id = null);

    public function user($user = null);

    public function save();

    public function delete();
}
