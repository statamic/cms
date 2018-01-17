<?php

namespace Statamic\CP\Publish;

use Statamic\Data\Processor;

trait ProcessesFields
{
    protected function addBlankFields($fieldset, $data = [])
    {
        return (new Processor($fieldset))->addBlankValues($data);
    }

    protected function preProcessFields($fieldset, $fields = [])
    {
        return (new Processor($fieldset))->preProcess($fields);
    }

    protected function processFields($fieldset, $fields = [], $filterNulls = true)
    {
        return (new Processor($fieldset))->process($fields, $filterNulls);
    }

    protected function preProcessWithBlankFields($fieldset, $fields = [], $filterNulls = true)
    {
        return (new Processor($fieldset))->preProcessWithBlanks($fields, $filterNulls);
    }
}
