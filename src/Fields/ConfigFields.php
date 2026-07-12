<?php

namespace Statamic\Fields;

class ConfigFields extends Fields
{
    protected function newField($handle, $config)
    {
        return new ConfigField($handle, $config);
    }
}
