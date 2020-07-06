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
        $data['error'] = $errors ? $this->getFirstErrorForEachField($errors) : [];
        $data['success'] = session()->get($successKey);

        return $data;
    }

    /**
     * Get first error for each field.
     *
     * @param \Illuminate\Support\MessageBag $messageBag
     * @return array
     */
    protected function getFirstErrorForEachField($messageBag)
    {
        return collect($messageBag->messages())
            ->map(function ($errors, $field) {
                return $errors[0];
            })
            ->all();
    }
}
