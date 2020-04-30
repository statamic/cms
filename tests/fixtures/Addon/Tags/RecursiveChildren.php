<?php

namespace Foo\Bar\Tags;

class RecursiveChildren extends \Statamic\Tags\Tags
{
    public function index()
    {
        return $this->parseLoop([
            [
                'title' => 'One',
                'children' => [
                    [
                        'title' => 'Two',
                    ],
                    [
                        'title' => 'Three',
                        'children' => [
                            [
                                'title' => 'Four',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
