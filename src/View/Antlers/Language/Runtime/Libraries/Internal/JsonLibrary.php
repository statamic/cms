<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\View\Antlers\Language\Exceptions\LibraryCallException;
use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class JsonLibrary extends RuntimeLibrary
{
    protected $name = 'json';

    public function __construct()
    {
        $this->exposedMethods = [
            'JSON_BIGINT_AS_STRING' => 1,
            'JSON_INVALID_UTF8_IGNORE' => 1,
            'JSON_INVALID_UTF8_SUBSTITUTE' => 1,
            'JSON_OBJECT_AS_ARRAY' => 1,
            'JSON_THROW_ON_ERROR' => 1,
            'JSON_HEX_TAG' => 1,
            'JSON_HEX_AMP' => 1,
            'JSON_HEX_APOS' => 1,
            'JSON_NUMERIC_CHECK' => 1,
            'JSON_PRETTY_PRINT' => 1,
            'JSON_UNESCAPED_SLASHES' => 1,
            'JSON_FORCE_OBJECT' => 1,
            'JSON_UNESCAPED_UNICODE' => 1,

            'decode' => [
                $this->stringVar('string'),
                $this->numericVarWithDefault('depth', 512),
                $this->numericVarWithDefault('options', 0),
            ],
            'parse' => [
                $this->stringVar('string'),
                $this->numericVarWithDefault('depth', 512),
                $this->numericVarWithDefault('options', 0),
            ],
            'encode' => [
                $this->anyParam('data'),
                $this->numericVarWithDefault('options', 0),
                $this->numericVarWithDefault('depth', 512),
            ],
            'encodeAttribute' => [
                $this->anyParam('data'),
            ],
            'stringify' => [
                $this->anyParam('data'),
                $this->numericVarWithDefault('options', 0),
                $this->numericVarWithDefault('depth', 512),
            ],
            'prettyPrint' => [
                $this->anyParam('data'),
            ],
        ];
    }

    public function JSON_UNESCAPED_UNICODE()
    {
        return JSON_UNESCAPED_UNICODE;
    }

    public function JSON_FORCE_OBJECT()
    {
        return JSON_FORCE_OBJECT;
    }

    public function JSON_UNESCAPED_SLASHES()
    {
        return JSON_UNESCAPED_SLASHES;
    }

    public function JSON_PRETTY_PRINT()
    {
        return JSON_PRETTY_PRINT;
    }

    public function JSON_HEX_TAG()
    {
        return JSON_HEX_TAG;
    }

    public function JSON_HEX_AMP()
    {
        return JSON_HEX_AMP;
    }

    public function JSON_HEX_APOS()
    {
        return JSON_HEX_APOS;
    }

    public function JSON_NUMERIC_CHECK()
    {
        return JSON_NUMERIC_CHECK;
    }

    public function JSON_BIGINT_AS_STRING()
    {
        return JSON_BIGINT_AS_STRING;
    }

    public function JSON_INVALID_UTF8_IGNORE()
    {
        return JSON_INVALID_UTF8_IGNORE;
    }

    public function JSON_INVALID_UTF8_SUBSTITUTE()
    {
        return JSON_INVALID_UTF8_SUBSTITUTE;
    }

    public function JSON_OBJECT_AS_ARRAY()
    {
        return JSON_OBJECT_AS_ARRAY;
    }

    public function JSON_THROW_ON_ERROR()
    {
        return JSON_THROW_ON_ERROR;
    }

    public function decode($string, $depth = 512, $options = 0)
    {
        return json_decode($$string, true, $depth, $options);
    }

    public function parse($string, $depth = 512, $options = 0)
    {
        $results = json_decode($string, true, $depth, $options);

        if (json_last_error() != 0) {
            throw new LibraryCallException('JSON Error: '.json_last_error_msg());
        }

        return $results;
    }

    public function encodeAttribute($data)
    {
        return htmlspecialchars(json_encode($data), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT, 'UTF-8');
    }

    public function stringify($data, $options = 0, $depth = 512)
    {
        $results = json_decode($data, true, $depth, $options);

        if (json_last_error() != 0) {
            throw new LibraryCallException('JSON Error: '.json_last_error_msg());
        }

        return $results;
    }

    public function encode($data, $options = 0, $depth = 512)
    {
        return json_encode($data, $options, $depth);
    }

    public function prettyPrint($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
