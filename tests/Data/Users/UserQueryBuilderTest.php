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

    /** @test **/
    public function users_are_found_using_nested_where()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();
        User::make()->email('aragorn@precious.com')->data(['name' => 'Aragorn'])->save();
        User::make()->email('bombadil@precious.com')->data(['name' => 'Tommy'])->save();
        User::make()->email('sauron@precious.com')->data(['name' => 'Sauron'])->save();

        $users = User::query()
            ->where(function ($query) {
                $query->where('name', 'Gandalf');
            })
            ->orWhere(function ($query) {
                $query->where('name', 'Frodo')->orWhere('name', 'Aragorn');
            })
            ->orWhere('email', 'sauron@precious.com')
            ->get();

        $this->assertCount(4, $users);
        $this->assertEquals(['Gandalf', 'Frodo', 'Aragorn', 'Sauron'], $users->map->name->all());
    }

    /** @test **/
    public function users_are_found_using_nested_where_in()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();
        User::make()->email('aragorn@precious.com')->data(['name' => 'Aragorn'])->save();
        User::make()->email('bombadil@precious.com')->data(['name' => 'Tommy'])->save();
        User::make()->email('sauron@precious.com')->data(['name' => 'Sauron'])->save();

        $users = User::query()
            ->where(function ($query) {
                $query->where('name', 'Gandalf');
            })
            ->orWhere(function ($query) {
                $query->where('name', 'Frodo')->orWhereIn('name', ['Gandalf', 'Aragorn']);
            })
            ->orWhere('email', 'sauron@precious.com')
            ->get();

        $this->assertCount(4, $users);
        $this->assertEquals(['Gandalf', 'Frodo', 'Aragorn', 'Sauron'], $users->map->name->all());
    }

    /** @test **/
    public function users_are_found_using_where_with_json_value()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf', 'content' => ['value' => 1]])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol', 'content' => ['value' => 2]])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo', 'content' => ['value' => 3]])->save();
        User::make()->email('aragorn@precious.com')->data(['name' => 'Aragorn', 'content' => ['value' => 1]])->save();
        User::make()->email('bombadil@precious.com')->data(['name' => 'Tommy', 'content' => ['value' => 2]])->save();
        User::make()->email('sauron@precious.com')->data(['name' => 'Sauron', 'content' => ['value' => 3]])->save();
        // the following two users use scalars for the content field to test that they get successfully ignored.
        User::make()->email('arwen@precious.com')->data(['name' => 'Arwen', 'content' => 'string'])->save();
        User::make()->email('bilbo@precious.com')->data(['name' => 'Bilbo', 'content' => 'string'])->save();

        $users = User::query()
            ->where('content->value', 1)
            ->get();

        $this->assertCount(2, $users);
        $this->assertEquals(['Gandalf', 'Aragorn'], $users->map->name->all());

        $users = User::query()
            ->where('content->value', '<>', 1)
            ->get();

        $this->assertCount(6, $users);
        $this->assertEquals(['Smeagol', 'Frodo', 'Tommy', 'Sauron', 'Arwen', 'Bilbo'], $users->map->name->all());
    }
}
