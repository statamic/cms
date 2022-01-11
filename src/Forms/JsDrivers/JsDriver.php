<?php

namespace Statamic\Forms\JsDrivers;

interface JsDriver
{
    public function handle();

    public function addToFormData($form, $data);

    public function addToFormAttributes($form);

    public function addToRenderableFieldData($field, $data);

    public function addToRenderableFieldAttributes($field);

    public function copyShowFieldToFormData($fields);
}
