<?php

namespace Tests\Fieldtypes;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\UserRoles;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_empty_index_items_without_assign_roles_permission()
    {
        $this->actingAs($this->cpUserWithPermissions(['access cp']));

        $items = $this->fieldtype()->getIndexItems(new Request);

        $this->assertTrue($items->isEmpty());
    }

    #[Test]
    public function it_returns_roles_in_index_items_with_assign_roles_permission()
    {
        $this->actingAs($this->cpUserWithPermissions(['access cp', 'assign roles']));

        $items = $this->fieldtype()->getIndexItems(new Request);

        $this->assertContains('editor', $items->pluck('id'));
    }

    private function fieldtype()
    {
        return (new UserRoles)->setField(new Field('test', ['type' => 'user_roles']));
    }

    private function cpUserWithPermissions(array $permissions)
    {
        $this->setTestRoles(['test' => $permissions, 'editor' => []]);

        return tap(User::make()->id(uniqid())->assignRole('test'))->save();
    }
}
