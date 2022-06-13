<?php

namespace Statamic\Policies;

class GlobalSetVariablesPolicy extends GlobalSetPolicy
{
    public function edit($user, $set)
    {
        return $this->view($user, $set);
    }
}
