<?php

namespace Tests\Feature\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateFieldTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function a_new_field_can_use_the_entry_handle()
    {
        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->postJson(cp_route('fields.update'), [
                'type' => 'text',
                'values' => ['display' => 'Entry', 'handle' => 'entry', 'isNew' => true],
            ])
            ->assertOk();
    }

    #[Test]
    public function a_new_field_cant_use_a_reserved_handle()
    {
        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->postJson(cp_route('fields.update'), [
                'type' => 'text',
                'values' => ['display' => 'If', 'handle' => 'if', 'isNew' => true],
            ])
            ->assertJsonValidationErrors('handle');
    }
}
