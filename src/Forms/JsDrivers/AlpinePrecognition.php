<?php

namespace Statamic\Forms\JsDrivers;

use Statamic\Statamic;

class AlpinePrecognition extends Alpine
{
    protected function parseOptions($options)
    {
        $this->scope = $options[0] ?? 'form';
        $this->component = $options[1] ?? null;
    }

    protected function renderAlpineXData($xData, $alpineScope)
    {
        $action = $this->form->actionUrl();
        $xData = Statamic::modify($xData)->toJson()->entities();
        $xData = "\$form('post', '{$action}', {$xData})";

        $errors = $this->getInitialFormErrors();
        if ($errors?->count()) {
            $errors = Statamic::modify($errors)->toJson()->entities();
            $xData .= ".setErrors({$errors})";
        }

        $xData = "{{$alpineScope}: {$xData}}";
        if ($this->component) {
            $xData = "{$this->component}({$xData})";
        }

        return $xData;
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
