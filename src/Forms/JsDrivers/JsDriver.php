<?php

namespace Statamic\Forms\JsDrivers;

use Statamic\Forms\ArrayableShowField;

interface JsDriver
{
    public function addToFormData($data);

    public function addToFormAttributes();

    public function addToRenderableFieldData($field, $data);

    public function addToRenderableFieldAttributes($field);

    public function render($html);

    public function copyShowFieldToFormData(array $fields): ArrayableShowField;
}
