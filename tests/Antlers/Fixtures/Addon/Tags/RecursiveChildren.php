<?php

namespace Tests\Antlers\Fixtures\Addon\Tags;

use Statamic\Tags\Tags;

class RecursiveChildren extends Tags
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
                                'foo' => 'Baz',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
