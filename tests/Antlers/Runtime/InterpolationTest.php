<?php

namespace Tests\Antlers\Runtime;

use Facades\Tests\Factories\EntryFactory;
use Tests\Antlers\ParserTestCase;

class InterpolationTest extends ParserTestCase
{
    public function test_interpolation_cache_is_cleared_between_nodes()
    {
        $template = <<<'EOT'
{{ partial:input id="email" }}{{ partial:input id="password" }}
EOT;

        $this->assertSame('<input id="email"><input id="password">', $this->renderString($template, [], true));
    }

    public function test_augmented_values_do_not_get_lost_when_inside_nested_partials()
    {
        $template = <<<'EOT'
{{ partial:augment_one }}
EOT;

        $entry = EntryFactory::collection('interpolation-test')->id('interpolation-one')->slug('interpolation-one')->data(['title' => 'The Title'])->create();

        $data = [
            'entry' => $entry,
            'condition' => true,
        ];

        $this->assertSame('<The Title>', trim(($this->renderString($template, $data, true))));
    }
}
