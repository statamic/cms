<?php

namespace Statamic\Tags;

class GetError extends GetErrors
{
    public function index()
    {
        return false;
    }

    public function wildcard(string $name)
    {
        if (! $errors = $this->getMessageBag()) {
            return false;
        }

        return ['message' => $errors->first($name)];
    }
}
