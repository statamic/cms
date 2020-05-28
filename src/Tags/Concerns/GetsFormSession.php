<?php

namespace Statamic\Tags\Concerns;

trait GetsFormSession
{
    /**
     * Get form session error/success output.
     *
     * @param string $handle
     */
    protected function getFormSession($key = 'default')
    {
        $data = [];

        $errorBagKey = $key;

        $successKey = $key !== 'default'
            ? "{$key}.success"
            : 'success';

        $errors = optional(session()->get('errors'))->getBag($errorBagKey);

        $data['errors'] = $errors ? $errors->all() : [];
        $data['error'] = $errors ? array_combine($errors->keys(), $data['errors']) : [];
        $data['success'] = session()->get($successKey);

        return $data;
    }
}
