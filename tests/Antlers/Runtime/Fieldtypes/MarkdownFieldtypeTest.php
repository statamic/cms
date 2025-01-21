<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Carbon\Carbon;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Markdown;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Cascade;
use Tests\Antlers\ParserTestCase;

class MarkdownFieldtypeTest extends ParserTestCase
{
    public function test_render_markdown_fieldtype()
    {
        $this->runFieldTypeTest('markdown');
    }

    public function test_markdown_with_antlers_evaluates_correctly()
    {
        $markdown = new Markdown();
        $field = new Field('test', [
            'type' => 'markdown',
            'antlers' => true,
        ]);
        $markdown->setField($field);

        $value = <<<'EOT'
{{ if 1 > 3 }}Yes.{{ else }}No.{{ /if }}
{{ if 1 < 3 && true == true }}Yes.{{ else }}No.{{ /if }}
{{ if 3 > 1 }}3 is bigger{{ /if }}
{{ now format="Y" }}
Just some content
EOT;

        $cascade = $this->mock(Cascade::class, function ($m) use ($markdown, $value) {
            $m->shouldReceive('get')->with('settings')->andReturn([
                'field_name' => new Value('{{ now format="Y" }}', 'field_name', $markdown),
                'another_field' => new Value($value, 'another_field', $markdown),
            ]);
        });

        $data = [
            'now' => Carbon::parse('2019-03-10 13:00'),
            'markdown' => new Value('<The test string"">', 'markdown', $markdown),
            'markdown_two' => new Value('Value: {{ now format="Y" }}', 'markdown_two', $markdown),
            'markdown_three' => new Value('Value: {{ now | format("Y") }}', 'markdown_three', $markdown),
            'markdown_four' => new Value("Value: {{ now | format('Y') }}", 'markdown_four', $markdown),
        ];

        $this->assertSame('<p>&lt;The test string&quot;&quot;&gt;</p>', trim($this->renderString('{{ markdown }}', $data)));
        $this->assertSame('<p>Value: 2019</p>', trim($this->renderString('{{ markdown_two }}', $data)));
        $this->assertSame('<p>Value: 2019</p>', trim($this->renderString('{{ markdown_three }}', $data)));
        $this->assertSame('<p>Value: 2019</p>', trim($this->renderString('{{ markdown_four }}', $data)));
        $this->assertSame('<p>2019</p>', trim((string) $this->parser()->cascade($cascade)->render('{{ settings:field_name }}', $data)));

        $expected = <<<'EOT'
<p>No.<br />
Yes.<br />
3 is bigger<br />
2019<br />
Just some content</p>
EOT;

        $this->assertSame($expected, trim(StringUtilities::normalizeLineEndings((string) $this->parser()->cascade($cascade)->render('{{ settings:another_field }}', $data))));
    }
}
