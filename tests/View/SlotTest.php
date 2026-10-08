<?php

namespace Tests\View;

use Illuminate\View\InvokableComponentVariable;
use RuntimeException;
use Statamic\View\Slot;
use Tests\FakesViews;
use Tests\TestCase;

class SlotTest extends TestCase
{
    use FakesViews;

    public function test_it_renders_with_data_params_and_props()
    {
        $slot = new Slot(fn ($data) => implode(',', array_filter([
            $data['outer'] ?? null,
            $data['params']['title'] ?? null,
            $data['n'] ?? null,
        ])), ['outer' => 'O']);

        $slot->withParams(['title' => 'T']);

        $this->assertSame('O,T', $slot->render());
        $this->assertSame('O,T,3', $slot->render(['n' => '3']));
    }

    public function test_a_closure_slot_serializes_as_rendered_content()
    {
        $slot = new Slot(fn ($data) => 'rendered:'.($data['outer'] ?? ''), ['outer' => 'O']);

        $revived = unserialize(serialize($slot));

        $this->assertSame('rendered:O', $revived->render());
        $this->assertSame('rendered:O', $revived->toHtml());
    }

    public function test_a_revived_closure_slot_rejects_props()
    {
        $slot = (new Slot(fn ($data) => 'x', []))->named('row');

        $revived = unserialize(serialize($slot));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The [row] slot has already been rendered and cached');

        $revived->render(['n' => '3']);
    }

    public function test_a_revived_blade_slot_renders_with_its_data_params_and_props()
    {
        $slot = Slot::forBlade('{{ $outer ?? "?" }},{{ $params["title"] ?? "?" }},{{ $n ?? "?" }}', ['outer' => 'O'])
            ->withParams(['title' => 'T']);

        $revived = unserialize(serialize($slot));

        $this->assertSame('O,T,?', $revived->render());
        $this->assertSame('O,T,3', $revived->render(['n' => '3']));
    }

    public function test_nested_slots_survive_serialization()
    {
        $inner = Slot::forBlade('inner:{{ $x ?? "?" }}', ['x' => 'X']);
        $outer = Slot::forBlade('outer({{ $nested }})', ['nested' => $inner]);

        $revived = unserialize(serialize($outer));

        $this->assertSame('outer(inner:X)', $revived->render());
    }

    public function test_a_revived_slot_survives_another_serialization_round_trip()
    {
        $slot = Slot::forBlade('{{ $outer }},{{ $n ?? "?" }}', ['outer' => 'O']);

        $twice = unserialize(serialize(unserialize(serialize($slot))));

        $this->assertSame('O,3', $twice->render(['n' => '3']));
    }

    public function test_component_scope_variables_are_resolved_when_cached()
    {
        $slot = Slot::forBlade('{{ $label }}', ['label' => new InvokableComponentVariable(fn () => 'V')]);

        $revived = unserialize(serialize($slot));

        $this->assertSame('V', $revived->render());
    }

    public function test_slot_content_matching_a_view_name_renders_as_content()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('footer', 'THE VIEW', 'blade.php');

        $slot = Slot::forBlade('footer', []);

        $this->assertSame('footer', $slot->render());
        $this->assertSame('footer', unserialize(serialize($slot))->render());
    }

    public function test_a_slot_with_unserializable_scope_throws_when_cached()
    {
        $slot = Slot::forBlade('{{ $outer ?? "?" }}', ['outer' => 'O', 'bad' => fn () => null])
            ->named('row');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The [row] slot cannot be cached because its scope contains values that cannot be serialized (bad)');

        serialize($slot);
    }
}
