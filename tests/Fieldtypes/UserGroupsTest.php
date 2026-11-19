<?php

namespace Tests\Fieldtypes;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\UserGroups;
use Tests\FakesRoles;
use Tests\FakesUserGroups;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserGroupsTest extends TestCase
{
    use FakesRoles;
    use FakesUserGroups;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_empty_index_items_without_assign_user_groups_permission()
    {
        $this->actingAs($this->cpUserWithPermissions(['access cp']));

        $items = $this->fieldtype()->getIndexItems(new Request);

        $this->assertTrue($items->isEmpty());
    }

    #[Test]
    public function it_returns_groups_in_index_items_with_assign_user_groups_permission()
    {
        $this->setTestUserGroups(['editors' => []]);
        $this->actingAs($this->cpUserWithPermissions(['access cp', 'assign user groups']));

        $items = $this->fieldtype()->getIndexItems(new Request);

        $this->assertContains('editors', $items->pluck('id'));
    }

    private function fieldtype()
    {
        return (new UserGroups)->setField(new Field('test', ['type' => 'user_groups']));
    }

    private function cpUserWithPermissions(array $permissions)
    {
        $this->setTestRoles(['test' => $permissions]);

        return tap(User::make()->id(uniqid())->assignRole('test'))->save();
    }
}
