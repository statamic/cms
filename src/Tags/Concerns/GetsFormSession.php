<?php

namespace Statamic\Tags\Concerns;

trait GetsFormSession
{
    /**
     * Get form session error/success output.
     *
     * @param  string  $formName
     * @return array
     */
    protected function getFormSession($formName = 'default')
    {
        $data = [];

        $errors = optional(session()->get('errors'))->getBag($formName);

        $data['errors'] = $errors ? $errors->all() : [];
        $data['error'] = $errors ? $this->getFirstErrorForEachField($errors) : [];
        $data['success'] = $this->getFromFormSession($formName, 'success');

        // Only include this boolean if it's actually passed in session;
        // It will be for form submissions, but not for user login/register submissions.
        if ($this->getFromFormSession($formName, 'submission_created')) {
            $data['submission_created'] = true;
        }

        // Only include this boolean if it's actually passed in session;
        // It will be for user registration submissions, but not form submissions.
        if ($this->getFromFormSession($formName, 'user_created')) {
            $data['user_created'] = true;
        }

        return $data;
    }

    /**
     * Get value from form session.
     *
     * @param  string  $formName
     * @param  string  $key
     * @return mixed
     */
    protected function getFromFormSession($formName, $key)
    {
        return session()->get(
            $formName !== 'default'
                ? "{$formName}.{$key}"
                : $key
        );
    }

    /**
     * Get first error for each field.
     *
     * @param  \Illuminate\Support\MessageBag  $messageBag
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
