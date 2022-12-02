<?php

namespace Tests\Tags\Concerns;

use Statamic\Facades\Antlers;
use Statamic\Fields\Field;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags;
use Tests\TestCase;

class RendersFormsTest extends TestCase
{
    const MISSING = 'field is missing from request';

    public function setUp(): void
    {
        parent::setUp();

        $this->tag = new FakeTagWithRendersForms;
    }

    /** @test */
    public function it_renders_form_open_tags()
    {
        $output = $this->tag->formOpen('http://localhost:8000/submit');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost:8000/submit">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_token" value="">', $output);
        $this->assertStringNotContainsString('<input type="hidden" name="_method"', $output);
    }

    /** @test */
    public function it_renders_form_open_tags_with_custom_method()
    {
        $output = $this->tag->formOpen('http://localhost:8000/submit', 'DELETE');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost:8000/submit">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_token" value="">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_method" value="DELETE">', $output);
    }

    /** @test */
    public function it_renders_form_open_tags_with_custom_attributes()
    {
        $output = $this->tag
            ->setParameters([
                'class' => 'mb-1',
                'attr:id' => 'form',
                'method' => 'this should not render',
                'action' => 'this should not render',
            ])
            ->formOpen('http://localhost:8000/submit', 'DELETE');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost:8000/submit" class="mb-1" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_token" value="">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_method" value="DELETE">', $output);
    }

    /** @test */
    public function it_renders_form_close_tag()
    {
        $this->assertEquals('</form>', $this->tag->formClose());
    }

    /** @test */
    public function it_minifies_space_between_field_html_elements()
    {
        $fields = <<<'EOT'
            <select>
                <option>One</option>
                <option>
                    Two
                </option>
            </select>
            <label>
                <input type="checkbox">
                Option <a href="/link">with link</a> text or <span class="tailwind">style</span> class
            </label>
            <label>
                <input type="radio">
                Intentionally<a href="/link">tight</a>link or<span class="tailwind">style</span>class
            </label>
            <textarea>
                Some <a href="/link">link</a> or <span class="tailwind">styled text
            </textarea>
            <textarea>
                <a href="/link">Start with</a> and end with a <a href="/link">link</a>
            </textarea>
EOT;

        $expected = '<select><option>One</option><option>Two</option></select><label><input type="checkbox">Option <a href="/link">with link</a> text or <span class="tailwind">style</span> class</label><label><input type="radio">Intentionally<a href="/link">tight</a>link or<span class="tailwind">style</span>class</label><textarea>Some <a href="/link">link</a> or <span class="tailwind">styled text</textarea><textarea><a href="/link">Start with</a> and end with a <a href="/link">link</a></textarea>';

        $this->assertEquals($expected, $this->tag->minifyFieldHtml($fields));
    }

    private function createField($type, $value, $default, $old)
    {
        $config = ['type' => $type];

        if ($default) {
            $config['default'] = $default;
        }

        $field = new Field('test', $config);
        $field->setValue($value);

        if ($old !== self::MISSING) {
            session()->flashInput(['test' => $old]);
            $this->get('/'); // create a request so the session works.
        }

        return $this->tag->getRenderableField($field);
    }

    /**
     * @test
     * @dataProvider renderTextProvider
     */
    public function renders_text_fields($value, $default, $old, $expected)
    {
        $rendered = $this->createField('text', $value, $default, $old);

        $this->assertSame($expected, $rendered['value']);
        $this->assertStringContainsString('value="'.$rendered['value'].'"', $rendered['field']);
    }

    public function renderTextProvider()
    {
        return [
            'no value, missing' => ['value' => null, 'default' => null, 'old' => self::MISSING, 'expectedValue' => null],
            'no value, filled' => ['value' => null, 'default' => null, 'old' => 'old', 'expectedValue' => 'old'],
            'no value, empty' => ['value' => null, 'default' => null, 'old' => null, 'expectedValue' => null],

            'value, missing' => ['value' => 'existing', 'default' => null, 'old' => self::MISSING, 'expectedValue' => 'existing'],
            'value, filled' => ['value' => 'existing', 'default' => null, 'old' => 'old', 'expectedValue' => 'old'],
            'value, empty' => ['value' => 'existing', 'default' => null, 'old' => null, 'expectedValue' => null],

            'no value, default, missing' => ['value' => null, 'default' => 'default', 'old' => self::MISSING, 'expectedValue' => 'default'],
            'no value, default, filled' => ['value' => null, 'default' => 'default', 'old' => 'old', 'expectedValue' => 'old'],
            'no value, default, empty' => ['value' => null, 'default' => 'default', 'old' => null, 'expectedValue' => null],

            'value, default, missing' => ['value' => 'existing', 'default' => 'default', 'old' => self::MISSING, 'expectedValue' => 'existing'],
            'value, default, filled' => ['value' => 'existing', 'default' => 'default', 'old' => 'old', 'expectedValue' => 'old'],
            'value, default, empty' => ['value' => 'existing', 'default' => 'default', 'old' => null, 'expectedValue' => null],
        ];
    }

    /**
     * @test
     * @dataProvider renderToggleProvider
     */
    public function renders_toggles($value, $default, $old, $expected)
    {
        $rendered = $this->createField('toggle', $value, $default, $old);

        $this->assertSame($expected, (bool) $rendered['value']);

        if ($expected) {
            $this->assertStringContainsString('checked', $rendered['field']);
        } else {
            $this->assertStringNotContainsString('checked', $rendered['field']);
        }
    }

    public function renderToggleProvider()
    {
        return [
            'no value, missing' => ['value' => null, 'default' => null, 'old' => self::MISSING, 'expectedValue' => false],
            'no value, checked' => ['value' => null, 'default' => null, 'old' => '1', 'expectedValue' => true],
            'no value, unchecked' => ['value' => null, 'default' => null, 'old' => '0', 'expectedValue' => false],

            'value true, missing' => ['value' => true, 'default' => null, 'old' => self::MISSING, 'expectedValue' => true],
            'value true, checked' => ['value' => true, 'default' => null, 'old' => '1', 'expectedValue' => true],
            'value true, unchecked' => ['value' => true, 'default' => null, 'old' => '0', 'expectedValue' => false],

            'value false, missing' => ['value' => false, 'default' => null, 'old' => self::MISSING, 'expectedValue' => false],
            'value false, checked' => ['value' => false, 'default' => null, 'old' => '1', 'expectedValue' => true],
            'value false, unchecked' => ['value' => false, 'default' => null, 'old' => '0', 'expectedValue' => false],

            'no value, default true, missing' => ['value' => null, 'default' => true, 'old' => self::MISSING, 'expectedValue' => true],
            'no value, default true, checked' => ['value' => null, 'default' => true, 'old' => '1', 'expectedValue' => true],
            'no value, default true, unchecked' => ['value' => null, 'default' => true, 'old' => '0', 'expectedValue' => false],

            'no value, default false, missing' => ['value' => null, 'default' => false, 'old' => self::MISSING, 'expectedValue' => false],
            'no value, default false, checked' => ['value' => null, 'default' => false, 'old' => '1', 'expectedValue' => true],
            'no value, default false, unchecked' => ['value' => null, 'default' => false, 'old' => '0', 'expectedValue' => false],

            'value true, default true, missing' => ['value' => true, 'default' => true, 'old' => self::MISSING, 'expectedValue' => true],
            'value true, default true, checked' => ['value' => true, 'default' => true, 'old' => '1', 'expectedValue' => true],
            'value true, default true, unchecked' => ['value' => true, 'default' => true, 'old' => '0', 'expectedValue' => false],

            'value true, default false, missing' => ['value' => true, 'default' => false, 'old' => self::MISSING, 'expectedValue' => true],
            'value true, default false, checked' => ['value' => true, 'default' => false, 'old' => '1', 'expectedValue' => true],
            'value true, default false, unchecked' => ['value' => true, 'default' => false, 'old' => '0', 'expectedValue' => false],

            'value false, default true, missing' => ['value' => false, 'default' => true, 'old' => self::MISSING, 'expectedValue' => false],
            'value false, default true, checked' => ['value' => false, 'default' => true, 'old' => '1', 'expectedValue' => true],
            'value false, default true, unchecked' => ['value' => false, 'default' => true, 'old' => '0', 'expectedValue' => false],
        ];
    }
}

class FakeTagWithRendersForms extends Tags
{
    use Concerns\RendersForms;

    public function __construct()
    {
        $this
            ->setParser(Antlers::parser())
            ->setContext([])
            ->setParameters([]);
    }

    public function __call($method, $arguments)
    {
        return $this->{$method}(...$arguments);
    }
}
