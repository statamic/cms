<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\FakesUserGroups;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UsersTagTest extends TestCase
{
    use FakesRoles,
        FakesUserGroups,
        PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_all_users()
    {
        User::make()->email('foo@bar.com')->save();
        User::make()->email('baz@bar.com')->save();
        User::make()->email('qux@bar.com')->save();

        $this->assertEquals('foo@bar.com|baz@bar.com|qux@bar.com|', $this->tag('{{ users }}{{ email }}|{{ /users }}'));
    }

    /** @test */
    public function it_gets_users_by_role()
    {
        $this->setTestRoles([
            'role1',
            'role2',
        ]);

        User::make()->email('foo@bar.com')->save();
        User::make()->email('baz@bar.com')->assignRole('role1')->save();
        User::make()->email('qux@bar.com')->assignRole('role2')->save();

        $this->assertEquals('baz@bar.com|', $this->tag('{{ users role="role1" }}{{ email }}|{{ /users }}'));
        $this->assertEquals('baz@bar.com|qux@bar.com|', $this->tag('{{ users role="role1|role2" }}{{ email }}|{{ /users }}'));
    }

    /** @test */
    public function it_gets_users_by_group()
    {
        $this->setTestUserGroups([
            'group1',
            'group2',
        ]);

        User::make()->email('foo@bar.com')->save();
        User::make()->email('baz@bar.com')->addToGroup('group1')->save();
        User::make()->email('qux@bar.com')->addToGroup('group2')->save();

        $this->assertEquals('baz@bar.com|', $this->tag('{{ users group="group1" }}{{ email }}|{{ /users }}'));
        $this->assertEquals('baz@bar.com|qux@bar.com|', $this->tag('{{ users group="group1|group2" }}{{ email }}|{{ /users }}'));
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }
}
