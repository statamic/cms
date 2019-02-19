<?php

namespace Foo\Bar\Tags;

use Statamic\Tags\Tags;

class Test extends Tags
{
    public function index()
    {
        return $this->get('variable');
    }

    /**
     * Takes a param of "var", gets the value from the context, and simply parses the contents.
     *
     * @return string
     */
    public function someParsing()
    {
        $var = $this->get('var');

        $val = array_get($this->context, $var);

        return $this->parse([$var => $val]);
    }
}
