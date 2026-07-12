<?php

namespace Tests\Antlers\Fixtures\Addon\Tags;

use Statamic\Support\Arr;
use Statamic\Tags\Tags;

class TestTags extends Tags
{
    public static $lastValue = null;

    public static $handle = 'test';

    public function index()
    {
        self::$lastValue = $this->params->get('variable');

        return self::$lastValue;
    }

    /**
     * Takes a param of "var", gets the value from the context, and simply parses the contents.
     *
     * @return string
     */
    public function someParsing()
    {
        $var = $this->params->get('var');

        $val = Arr::get($this->context, $var);

        return $this->parse([$var => $val]);
    }

    public function someLoopParsing()
    {
        return $this->parseLoop([
            [], [],
        ]);
    }

    public function returnSimpleArray()
    {
        return ['one' => 'a', 'two' => 'b'];
    }

    public function returnMultidimensionalArray()
    {
        return [
            ['one' => 'a', 'two' => 'b'],
            ['one' => 'c', 'two' => 'd'],
        ];
    }

    public function returnEmptyArray()
    {
        return [];
    }

    public function returnCollection()
    {
        return collect($this->returnMultidimensionalArray());
    }
}
