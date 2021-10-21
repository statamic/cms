<?php

namespace Tests\Data\Users;

use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserQueryBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test **/
    public function users_are_found_using_or_where()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();

        $users = User::query()->where('email', 'gandalf@precious.com')->orWhere('name', 'Frodo')->get();

        $this->assertCount(2, $users);
        $this->assertEquals(['Gandalf', 'Frodo'], $users->map->name->all());
    }

    /** @test **/
    public function users_are_found_using_or_where_in()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();
        User::make()->email('aragorn@precious.com')->data(['name' => 'Aragorn'])->save();
        User::make()->email('bombadil@precious.com')->data(['name' => 'Tommy'])->save();

        $users = User::query()->whereIn('name', ['Gandalf', 'Frodo'])->orWhereIn('name', ['Gandalf', 'Aragorn', 'Tommy'])->get();

        $this->assertCount(4, $users);
        $this->assertEquals(['Gandalf', 'Frodo', 'Aragorn', 'Tommy'], $users->map->name->all());
    }

    /** @test **/
    public function users_are_found_using_or_where_not_in()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();
        User::make()->email('aragorn@precious.com')->data(['name' => 'Aragorn'])->save();
        User::make()->email('bombadil@precious.com')->data(['name' => 'Tommy'])->save();
        User::make()->email('sauron@precious.com')->data(['name' => 'Sauron'])->save();

        $users = User::query()->whereNotIn('name', ['Gandalf', 'Frodo'])->orWhereNotIn('name', ['Gandalf', 'Sauron'])->get();

        $this->assertCount(3, $users);
        $this->assertEquals(['Smeagol', 'Aragorn', 'Tommy'], $users->map->name->all());
    }
}
