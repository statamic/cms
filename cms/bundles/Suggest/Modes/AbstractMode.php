<?php

namespace Statamic\Addons\Suggest\Modes;

use Statamic\API\Str;
use Statamic\API\Parse;
use Statamic\Addons\Suggest\Mode;
use Statamic\Addons\Suggest\RequestAdapter;

abstract class AbstractMode implements Mode
{
    protected $config;
    protected $request;

    public function __construct($config)
    {
        $this->config = $config;
        $this->request = new RequestAdapter($config);
    }

    protected function label($object, $default)
    {
        $label = $this->request->input('label', $default);

        // Placeholders have been specified, we'll need to parse the label.
        if (Str::contains($label, '{')) {
            return Parse::template($label, $object->toArray());
        }

        return method_exists($object, $label)
            ? $object->$label()
            : $object->get($label);
    }
}
