<?php

namespace Statamic\Addons\Test;

use Statamic\Extend\Tags;

class TestTags extends Tags
{
    public function index()
    {
        return $this->get('variable');
    }

    public function nav()
    {
        return $this->parseLoop([
            [
                'title' => 'One',
                'children' => [
                    [
                        'title' => 'Two'
                    ],
                    [
                        'title' => 'Three',
                        'children' => [
                            [
                                'title' => 'Four'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
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
