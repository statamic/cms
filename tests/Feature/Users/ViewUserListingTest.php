<?php

namespace Tests\Feature\Users;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewUserListingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_passes_the_columns_to_the_listing()
    {
        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('users.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('users/Index')
                ->where('columns', fn ($columns) => $this->visibility($columns) == [
                    'email' => true,
                    'name' => true,
                    'roles' => true,
                    'groups' => false,
                    'last_login' => true,
                    'two_factor' => false,
                ])
            );
    }

    #[Test]
    public function it_passes_the_columns_to_the_group_listing()
    {
        tap(UserGroup::make()->handle('editors')->title('Editors'))->save();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('user-groups.show', 'editors'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('user-groups/Show')
                ->where('listingConfig.columns', fn ($columns) => $this->visibility($columns)['email'] === true)
            );
    }

    private function visibility($columns): array
    {
        return collect(json_decode(json_encode($columns), true))
            ->mapWithKeys(fn (array $column) => [$column['field'] => $column['visible']])
            ->all();
    }
}
