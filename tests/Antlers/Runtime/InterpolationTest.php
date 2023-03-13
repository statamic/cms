<?php

namespace Tests\Antlers\Runtime;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Fields\LabeledValue;
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
{{ entry:title }}
{{ /partial:augment_one }}
EOT;

        $entry = EntryFactory::collection('interpolation-test')->id('interpolation-one')->slug('interpolation-one')->data(['title' => 'The Title'])->create();

        $data = [
            'entry' => $entry,
            'condition' => true,
        ];

        $this->assertSame('<The Title>', trim($this->renderString($template, $data, true)));
    }

    public function test_interpolation_inside_dynamic_access()
    {
        $valueZero = new LabeledValue('0', 'zero');
        $valueOne = new LabeledValue('1', 'one');
        $valueTwo = new LabeledValue('2', 'two');

        $data = [
            'items' => [
                ['title' => 'One'],
                ['title' => 'Two'],
                ['title' => 'Three'],
            ],
            'chars' => [
                't', 'i', 't', 'l', 'e',
            ],
            'var_zero' => 0,
            'var_one' => 1,
            'var_two' => 2,
            'var_title' => 'title',
            'value_zero' => $valueZero,
            'value_one' => $valueOne,
            'value_two' => $valueTwo,
        ];

        $this->assertSame('One', $this->renderString('{{ items.{value_zero}.title }}', $data));
        $this->assertSame('Two', $this->renderString('{{ items.{value_one}.title }}', $data));
        $this->assertSame('Three', $this->renderString('{{ items.{value_two}.title }}', $data));
        $this->assertSame('One', $this->renderString('{{ items[var_zero][var_title] }}', $data));
        $this->assertSame('Two', $this->renderString('{{ items[var_one][var_title] }}', $data));
        $this->assertSame('Three', $this->renderString('{{ items[var_two][var_title] }}', $data));
        $this->assertSame('One', $this->renderString('{{ items[var_zero]["title"] }}', $data));
        $this->assertSame('Two', $this->renderString('{{ items[var_one]["title"] }}', $data));
        $this->assertSame('Three', $this->renderString('{{ items[var_two]["title"] }}', $data));
        $this->assertSame('One', $this->renderString('{{ items[var_zero][{"title"}] }}', $data));
        $this->assertSame('Two', $this->renderString('{{ items[var_one][{"title"}] }}', $data));
        $this->assertSame('Three', $this->renderString('{{ items[var_two][{"title"}] }}', $data));
        $this->assertSame('One', $this->renderString('{{ items.{var_zero}[{"title"}] }}', $data));
        $this->assertSame('Two', $this->renderString('{{ items.{var_one}[{"title"}] }}', $data));
        $this->assertSame('Three', $this->renderString('{{ items.{var_two}[{"title"}] }}', $data));
        $this->assertSame('One', $this->renderString('{{ items.{var_zero}.{"title"} }}', $data));
        $this->assertSame('Two', $this->renderString('{{ items.{var_one}.{"title"} }}', $data));
        $this->assertSame('Three', $this->renderString('{{ items.{var_two}.{"title"} }}', $data));
        $this->assertSame('One', $this->renderString('{{ items.{var_zero}.title }}', $data));
        $this->assertSame('Two', $this->renderString('{{ items.{var_one}.title }}', $data));
        $this->assertSame('Three', $this->renderString('{{ items.{var_two}.title }}', $data));
        $this->assertSame('One', $this->renderString('{{ items.0.title }}', $data));
        $this->assertSame('Two', $this->renderString('{{ items.1.title }}', $data));
        $this->assertSame('Three', $this->renderString('{{ items.2.title }}', $data));

        $this->assertSame('One', $this->renderString('{{ items.0.{chars|join("")} }}', $data));
        $this->assertSame('Two', $this->renderString('{{ items.1.{chars|join("")} }}', $data));
        $this->assertSame('Three', $this->renderString('{{ items.2.{chars|join("")} }}', $data));

        $this->assertSame('<><One>|<One><Two>|<Two><Three>', $this->renderString(
            '{{ items }}<{{ items.{index - 1}.title }}><{{ title }}>{{ unless last }}|{{ /unless }}{{ /items }}',
            $data
        ));

        $this->assertSame('<One><One><Two><Two><Three><Three>', $this->renderString(
            '{{ items }}<{{ items[{index}]title }}><{{ title }}>{{ /items }}',
            $data
        ));

        $this->assertSame('<><One>|<One><Two>|<Two><Three>', $this->renderString(
            '{{ items }}<{{ items[{index - 1}]title }}><{{ title }}>{{ unless last }}|{{ /unless }}{{ /items }}',
            $data
        ));

        $template = <<<'EOT'
{{ items }}
<prev:{{ items[{index - 1}]title }}>
<cur:{{ title }}>
<next:{{ items[{index + 1}]title }}>
{{ /items }}
EOT;

        $expected = <<<'EOT'
<prev:>
<cur:One>
<next:Two>

<prev:One>
<cur:Two>
<next:Three>

<prev:Two>
<cur:Three>
<next:>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, $data)));

        $template = <<<'EOT'
{{ the_current_item = 0; }}
{{ items }}
<prev:{{ items[{the_current_item - 1}]title }}>
<cur:{{ title }}>
<next:{{ items[{the_current_item + 1}]title }}>
{{ the_current_item += 1; }}
{{ /items }}
EOT;

        $expected = <<<'EOT'
<prev:>
<cur:One>
<next:Two>


<prev:One>
<cur:Two>
<next:Three>


<prev:Two>
<cur:Three>
<next:>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, $data)));

        $template = <<<'EOT'
{{ the_current_item = 0; }}
{{ items }}
<prev:{{ items[{the_current_item - 1}]['title'] }}>
<cur:{{ title }}>
<next:{{ items[{the_current_item + 1}]['title'] }}>
{{ the_current_item += 1; }}
{{ /items }}
EOT;

        $this->assertSame($expected, trim($this->renderString($template, $data)));
    }
}
