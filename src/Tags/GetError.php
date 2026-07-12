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

        if (! $error = $errors->first($name)) {
            return false;
        }

        return ['message' => $error];
    }
}
