<?php

namespace Statamic\Forms\JsDrivers;

interface JsDriver
{
    public function handle();

    public function addToFormAttributes($attrs, $form);

    public function addToFormData($data, $form);

    public function addToRenderableFieldData($data, $field);

    public function copyShowFieldToFormData($fields);
}
