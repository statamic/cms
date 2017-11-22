<?php

namespace Statamic\Addons\Yaml;

use Statamic\API\YAML;
use Statamic\Extend\Fieldtype;

class YamlFieldtype extends Fieldtype
{
    /**
     * @var string
     */
    protected $snake_name = 'yaml';

    public function preProcess($data)
    {
        // When it's a config value being processed, we want to leave it as an array.
        // For example, the `settings` config in the Redactor fieldtype should be an
        // array when being passed into the fieldtype, but should be converted to a
        // string when inside the fieldset builder and we're editing the actual YAML.
        return ($this->is_config)
            ? $data
            : (is_array($data) ? YAML::dump($data) : $data);
    }

    public function process($data)
    {
        if (substr_count($data, "\n") > 0 || substr_count($data, ': ') > 0) {
            $data = YAML::parse($data);
        }

        if (empty($data)) {
            $data = null;
        }

        return $data;
    }
}
