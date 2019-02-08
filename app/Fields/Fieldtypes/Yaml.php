<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Yaml extends Fieldtype
{
    public function preProcess($data)
    {
        // Turn the YAML back into a string
        return \Statamic\API\YAML::dump($data);
    }

    public function process($data)
    {
        if (substr_count($data, "\n") > 0 || substr_count($data, ': ') > 0) {
            $data = \Statamic\API\YAML::parse($data);
        }

        if (empty($data)) {
            $data = null;
        }

        return $data;
    }
}
