<?php

namespace Statamic\Fields;

use Statamic\Facades\Fieldset;
use Statamic\Support\Str;

class FieldsetNamespace
{
    protected $name;
    protected $directory;
    protected $saveToSelf = false;

    public function __construct($name, $directory)
    {
        $this->name = $name;
        $this->directory = $directory;
    }

    public function directory()
    {
        return $this->directory;
    }

    public function saveDirectory()
    {
        if ($this->saveToSelf) {
            return $this->directory;
        }

        return Fieldset::directory().'/vendor/'.$this->name;
    }

    public function saveToSelf()
    {
        $this->saveToSelf = true;

        return $this;
    }

    public function saveToVendor()
    {
        $this->saveToSelf = false;

        return $this;
    }

    public function title()
    {
        return Str::of($this->name)->replace('_', ' ')->title();
    }
}
