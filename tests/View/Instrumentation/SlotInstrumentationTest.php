<?php

namespace Tests\View\Instrumentation;

use RuntimeException;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Statamic\View\Slot;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\View\Instrumentation\Concerns\DecodesMarkers;

class SlotInstrumentationTest extends ParserTestCase
{
    use DecodesMarkers, FakesViews;

    public function test_rendering_keeps_markers_while_values_and_serialized_forms_do_not()
    {
        $instrumenter = HtmlInstrumentation::make();
        Runtime::register($instrumenter);
        $rendered = str_replace('{{ title }}', 'Hello', $instrumenter("\n <p>{{ title }}</p> \n"));
        $slot = (new Slot(fn (array $data) => $rendered, ['title' => 'Hello']))->named('body');

        $this->assertSame(['title'], $this->markerExpressions($rendered));
        $this->assertSame(trim($rendered), $slot->render());
        $this->assertSame(trim($rendered), Slot::output($slot));
        $this->assertSame('<p>Hello</p>', $slot->toHtml());
        $this->assertSame('<p>Hello</p>', (string) $slot);
        $this->assertSame(['name' => 'body', 'rendered' => '<p>Hello</p>'], $slot->__serialize());
    }

    public function test_whitespace_around_edge_markers_is_trimmed_like_plain_output()
    {
        $instrumenter = HtmlInstrumentation::make();
        Runtime::register($instrumenter);
        $inner = str_replace('{{ body }}', 'Hello', substr($instrumenter('<div>{{ body }}</div>'), strlen('<div>'), -strlen('</div>')));
        $marked = " \n".$inner."\n ";
        $slot = new Slot(fn () => $marked);

        $this->assertStringStartsWith('<!-- antlers:start ', $inner);
        $this->assertStringEndsWith(' -->', $inner);
        $this->assertSame($inner, $slot->render());
        $this->assertSame('Hello', (string) $slot);
        $this->assertSame(['name' => null, 'rendered' => 'Hello'], $slot->__serialize());
    }

    public function test_pre_rendered_slots_restore_as_static_marker_free_content()
    {
        $instrumenter = HtmlInstrumentation::make();
        Runtime::register($instrumenter);
        $rendered = str_replace('{{ title }}', 'Hello', $instrumenter('<p>{{ title }}</p>'));
        $slot = (new Slot(fn () => $rendered))->named('footer');

        $restored = unserialize(serialize($slot));

        $this->assertInstanceOf(Slot::class, $restored);
        $this->assertSame('<p>Hello</p>', $restored->render());
        $this->assertSame('<p>Hello</p>', (string) $restored);
        $this->assertSame(['name' => 'footer', 'rendered' => '<p>Hello</p>'], $restored->__serialize());

        try {
            $restored->render(['extra' => true]);
            $this->fail('Expected a static slot to reject props.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('[footer] slot has already been rendered and cached', $exception->getMessage());
        }
    }

    public function test_output_renders_slots_with_props_and_escapes_anything_else()
    {
        $slot = (new Slot(fn (array $data) => '<i>'.$data['greeting'].' '.$data['title'].' '.$data['params']['x'].'</i>', ['title' => 'Hello']))
            ->withParams(['x' => 1]);

        $this->assertSame('<i>Hi Hello 1</i>', Slot::output($slot, ['greeting' => 'Hi']));
        $this->assertSame('a &lt;b&gt;', Slot::output('a <b>'));
    }

    public function test_antlers_slots_restored_from_a_cache_keep_the_slot_contract()
    {
        $this->app['statamic.tags']['capture_slot'] = CaptureSlotTag::class;
        $this->withFakeViews();
        $this->viewShouldReturnRaw('card', '<div>{{ capture_slot }}{{ slot }}</div>');
        $this->viewShouldReturnRaw('page', '<section>{{ include:card }}<b>{{ title }}</b>{{ /include:card }}</section>');
        Runtime::register(HtmlInstrumentation::make());

        $rendered = view('page', ['title' => 'Hello'])->render();
        $slot = CaptureSlotTag::$slot;

        $this->assertInstanceOf(Slot::class, $slot);
        $this->assertSame('<section><div><b>Hello</b></div></section>', Runtime::removeMarkers($rendered));
        $this->assertSame(['title'], $this->markerExpressions($slot->render()));
        $this->assertSame('<b>Hello</b>', (string) $slot);

        $restored = unserialize(serialize($slot));

        $this->assertNotSame($slot, $restored);
        $this->assertSame(['title'], $this->markerExpressions($restored->render()));
        $this->assertSame('<b>Hello</b>', Runtime::removeMarkers($restored->render()));
        $this->assertSame('<b>Hello</b>', (string) $restored);
        $this->assertSame('<b>Hello</b>', $restored->toHtml());
    }
}

class CaptureSlotTag extends Tags
{
    public static ?Slot $slot = null;

    public function index()
    {
        static::$slot = $this->context->raw('slot');

        return '';
    }
}
