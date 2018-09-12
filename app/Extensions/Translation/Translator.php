<?php

namespace Statamic\Extensions\Translation;

use Illuminate\Translation\Translator as BaseTranslator;

class Translator extends BaseTranslator
{
    public function parseKey($key)
    {
        if (starts_with($key, 'validation.')) {
            $key = 'statamic::' . $key;
        }

        return parent::parseKey($key);
    }
}
