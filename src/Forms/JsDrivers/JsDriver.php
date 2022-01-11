<?php

namespace Statamic\Forms\JsDrivers;

interface JsDriver
{
    public function handle();

    public function addToFormData($data, $form);

    public function addToFormAttributes($form);

    public function addToRenderableFieldData($data, $field);

    public function addToRenderableFieldAttributes($field);

    public function copyShowFieldToFormData($fields);
}
