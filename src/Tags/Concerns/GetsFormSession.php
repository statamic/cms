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

        if ($errors = session()->get('errors')) {
            $data['errors'] = $errors->getBag($errorBagKey)->all();
            $data['error'] = array_combine($errors->getBag($errorBagKey)->keys(), $data['errors']);
        }

        if ($success = session()->get($successKey)) {
            $data['success'] = $success;
        }

        return $data;
    }
}
