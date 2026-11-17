<?php

namespace Tests\View\Instrumentation;

use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Tests\Tags\Form\FormTestCase;
use Tests\View\Instrumentation\Concerns\DecodesMarkers;

class FormInstrumentationTest extends FormTestCase
{
    use DecodesMarkers;

    public function test_form_field_bodies_are_instrumented_once_per_field()
    {
        Runtime::register(HtmlInstrumentation::make());

        $output = (string) $this->tag('{{ form:contact }}<div>{{ form:fields }}<b>{{ handle }}</b>{{ /form:fields }}</div>{{ /form:contact }}');

        $handles = collect($this->decodedMarkers($output))->where('expression', 'handle');

        $this->assertGreaterThan(0, $handles->count());
        $this->assertSame(substr_count($output, '<b>'), $handles->count());
        $this->assertCount(1, $handles->pluck('start')->unique());
    }

    public function test_field_slots_rendered_by_fieldtype_views_are_instrumented_once()
    {
        $this->createForm(['fields' => [
            ['handle' => 'group_one', 'field' => ['type' => 'group', 'fields' => [
                ['handle' => 'alpha', 'field' => ['type' => 'text']],
                ['handle' => 'bravo', 'field' => ['type' => 'text']],
            ]]],
        ]], 'grouped');

        $template = '{{ form:grouped }}{{ form:fields }}<b>{{ handle }}</b>{{ field }}{{ /form:fields }}{{ /form:grouped }}';
        $plain = (string) $this->tag($template);

        $this->assertSame(3, substr_count($plain, '<b>'));

        Runtime::register(HtmlInstrumentation::make());

        $output = (string) $this->tag($template);
        $handles = collect($this->decodedMarkers($output))->where('expression', 'handle');

        $this->assertSame($plain, Runtime::removeMarkers($output));
        $this->assertSame(3, $handles->count());
        $this->assertSame(3, substr_count($output, '<b><!-- antlers:start '));
        $this->assertCount(1, $handles->pluck('start')->unique());
    }
}
