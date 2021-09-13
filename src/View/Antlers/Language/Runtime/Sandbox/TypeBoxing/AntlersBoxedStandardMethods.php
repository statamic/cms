<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox\TypeBoxing;

use Statamic\Support\Str;

trait AntlersBoxedStandardMethods
{
    public function toString()
    {
        if (is_bool($this->value)) {
            if ($this->value == true) {
                return 'true';
            } else {
                return 'false';
            }
        }

        if (is_array($this->value)) {
            return implode(',', $this->value);
        }

        return strval($this->value);
    }

    public function toInt()
    {
        if (is_array($this->value)) {
            return count($this->value);
        }

        return intval($this->value);
    }

    public function toFloat()
    {
        if (is_array($this->value)) {
            return floatval(count($this->value));
        }

        return floatval($this->value);
    }

    public function toBool()
    {
        if (is_string($this->value)) {
            return Str::toBool($this->value);
        }

        return boolval($this->value);
    }
}
