<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class RecursiveNodesTest extends ParserTestCase
{
    public function test_recursive_node_can_be_root()
    {
        $this->parseNodes(<<<'EOT'
    $template = <<<'EOT'
{{ records }}
    {{ title }}
    <br />
    {{ children }}
        {{ title }}
        <br />
        {{ *recursive children* }}
    {{ /children }}
{{ /records }}
EOT
);

        // The parseNodes will throw an exception if it fails to parse correctly.
        // We will just assert true is true to shut up the risky assertions warning.
        $this->assertTrue(true);
    }

    public function test_sub_recursive_nodes()
    {
        $data = [
            'records' => [
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
                                'children' => [
                                    [
                                        'title' => 'Five',
                                        'colors' => [
                                            [
                                                'name' => 'Blue',
                                                'colors' => [
                                                    [
                                                        'name' => 'Green',
                                                        'colors' => [
                                                            [
                                                                'name' => 'Yellow',
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $template = <<<'EOT'
<ul>
{{ records }}
<li>
{{ title }} - {{ depth }} - {{ children_depth }}

{{ if colors }}
Start Colors
{{ colors }}
Outer Title: {{ title }}
Color Name: {{ name }}
Active Depth: {{ depth }}
Outer Depth: {{ children_depth }}
Color Depth: {{ colors_depth }}

{{ if colors }}
REC-COLOR-START
{{ *subrecursive colors* }}
REC-COLOR-STOP
{{ /if }}
{{ /colors }}

End Colors
{{ /if }}

{{ if children }}
<ul>
{{ *recursive children* }}
</ul>
{{ /if }}
</li>
{{ /records }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul>

<li>
One - 1 - 1




<ul>

<li>
Two - 2 - 2




</li>

<li>
Three - 2 - 2




<ul>

<li>
Four - 3 - 3




<ul>

<li>
Five - 4 - 4


Start Colors

Outer Title: Five
Color Name: Blue
Active Depth: 1
Outer Depth: 4
Color Depth: 1


REC-COLOR-START

Outer Title: Five
Color Name: Green
Active Depth: 2
Outer Depth: 4
Color Depth: 2


REC-COLOR-START

Outer Title: Five
Color Name: Yellow
Active Depth: 3
Outer Depth: 4
Color Depth: 3



REC-COLOR-STOP


REC-COLOR-STOP



End Colors



</li>

</ul>

</li>

</ul>

</li>

</ul>

</li>

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings($this->renderString($template, $data)));
    }

    public function test_simple_depth_tree_class()
    {
        $data = [
            'records' => [
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
                                'children' => [
                                    [
                                        'title' => 'Five',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $template = <<<'EOT'
<ul>
{{ records }}
<li>
<span>{{ title }} -- {{ depth }}</span>

{{ if children }}
<ul class="depth-{{ depth ?? 'root' }}">
{{ *recursive children* }}
</ul>
{{ /if }}
</li>
{{ /records }}
</ul>
EOT;
        $expected = <<<'EOT'
<ul>

<li>
<span>One -- 1</span>


<ul class="depth-1">

<li>
<span>Two -- 2</span>


</li>

<li>
<span>Three -- 2</span>


<ul class="depth-2">

<li>
<span>Four -- 3</span>


<ul class="depth-3">

<li>
<span>Five -- 4</span>


</li>

</ul>

</li>

</ul>

</li>

</ul>

</li>

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings(
            $this->renderString($template, $data)
        ));
    }

    public function test_recursive_node_that_is_not_from_a_tag()
    {
        $data = [
            'records' => [
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
                                'children' => [
                                    ['title' => 'Five'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $template = <<<'EOT'
<ul>
{{ records }}
<li>{{ title }} - {{ depth }}
{{ if children }}
<ul>
{{ *recursive children* }}
</ul>
{{ /if }}
</li>
{{ /records }}
</ul>
EOT;
        $results = $this->renderString($template, $data);
        $expected = <<<'EOT'
<ul>

<li>One - 1

<ul>

<li>Two - 2

</li>

<li>Three - 2

<ul>

<li>Four - 3

<ul>

<li>Five - 4

</li>

</ul>

</li>

</ul>

</li>

</ul>

</li>

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings($results));
    }
}
