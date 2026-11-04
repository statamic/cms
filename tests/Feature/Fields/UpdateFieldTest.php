<?php

namespace Tests\Feature\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateFieldTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function update(string $type, array $values)
    {
        return $this
            ->actingAs(User::make()->makeSuper()->save())
            ->postJson('/cp/fields/update', [
                'type' => $type,
                'values' => array_merge(['handle' => 'test', 'display' => 'Test'], $values),
            ]);
    }

    #[Test]
    public function the_users_default_may_not_exceed_max_items()
    {
        $this
            ->update('users', ['max_items' => '2', 'default' => ['current', 'foo', 'bar']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['default' => 'The number of default users may not be greater than the field’s max items (2).']);
    }

    #[Test]
    public function the_users_default_may_equal_max_items()
    {
        $this
            ->update('users', ['max_items' => 2, 'default' => ['current', 'foo']])
            ->assertOk()
            ->assertJsonPath('default', ['current', 'foo']);
    }

    #[Test]
    public function the_users_default_is_unbounded_without_max_items()
    {
        $this
            ->update('users', ['default' => ['current', 'foo', 'bar']])
            ->assertOk()
            ->assertJsonPath('default', ['current', 'foo', 'bar']);
    }
}
