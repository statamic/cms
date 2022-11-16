<?php

namespace Statamic\Forms\JsDrivers;

interface JsDriver
{
    public function addToFormData($data);

    public function addToFormAttributes();

    public function addToRenderableFieldData($field, $data);

    public function addToRenderableFieldAttributes($field);

    public function render($html);

    public function copyShowFieldToFormData($fields);
}
