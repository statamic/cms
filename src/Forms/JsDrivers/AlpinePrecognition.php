<?php

namespace Statamic\Forms\JsDrivers;

use Statamic\Statamic;

class AlpinePrecognition extends Alpine
{
    protected function parseOptions($options)
    {
        $this->scope = $options[0] ?? 'form';
    }

    protected function renderAlpineXData($data, $alpineScope)
    {
        $action = $this->form->actionUrl();
        $data = Statamic::modify($data)->toJson()->entities();
        $call = "\$form('post', '{$action}', {$data})";

        $errors = $this->getInitialFormErrors();
        if ($errors?->count()) {
            $errors = Statamic::modify($errors)->toJson()->entities();
            $call .= ".setErrors({$errors})";
        }

        return "{{$alpineScope}: {$call}}";
    }

    public function addToRenderableFieldAttributes($field)
    {
        return array_merge(parent::addToRenderableFieldAttributes($field), [
            '@change' => "{$this->scope}.validate('{$field->handle()}')",
        ]);
    }

    protected function getInitialFormErrors()
    {
        return session()->get('errors')?->getBag('form.'.$this->form->handle());
    }
}
