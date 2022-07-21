<?php

namespace Statamic\Tags;

use Illuminate\Support\MessageBag;

class GetErrors extends Tags
{
    public function index()
    {
        if (! $errors = $this->getMessageBag()) {
            return false;
        }

        return [
            'fields' => collect($errors->getMessages())->map(function ($errors, $field) {
                return [
                    'field' => $field,
                    'messages' => $this->messages($errors),
                ];
            })->values()->all(),
        ];
    }

    public function all()
    {
        if (! $errors = $this->getMessageBag()) {
            return false;
        }

        return ['messages' => $this->messages($errors->all())];
    }

    /**
     * {{ get_errors:fieldname }}.
     */
    public function wildcard(string $name)
    {
        if (! $errors = $this->getMessageBag()) {
            return false;
        }

        if (empty($errors = $errors->get($name))) {
            return false;
        }

        return ['messages' => $this->messages($errors)];
    }

    protected function getMessageBag(): ?MessageBag
    {
        /** @var \Illuminate\Support\ViewErrorBag */
        $errors = view()->shared('errors');

        $messages = $errors->getBag($this->params->get('bag', 'default'));

        return $messages->isEmpty() ? null : $messages;
    }

    private function messages(array $messages): array
    {
        return collect($messages)->map(function ($message) {
            return ['message' => $message];
        })->all();
    }
}
