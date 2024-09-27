<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\FakesUserGroups;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UsersTagsTest extends TestCase
{
    use FakesRoles,
        FakesUserGroups,
        PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    #[Test]
    public function it_renders()
    {
        $user = User::make()->email('foo@bar.com')->save();

        $this->assertEquals('foo@bar.com', $this->tag('{{ users }}{{email}}{{ /users }}'));
    }

    #[Test]
    public function it_only_shows_users_in_groups()
    {
        $this->setTestRoles([
            'webmaster' => ['super'],
        ]);

        $this->setTestUserGroups([
            'favourite' => ['webmaster'], // Though super users have permission to do everything, they do not inherit all groups
            'non_favourite',
            'another_non_favourite',
        ]);

        $user = User::make()->email('foo@bar.com')->addToGroup('favourite')->save();

        $this->assertEquals('foo@bar.com', $this->tag('{{ users group="favourite" }}{{ email }}{{ /users }}'));
        $this->assertEquals('', $this->tag('{{ users group="non_favourite" }}{{ email }}{{ /users }}'));

        $this->assertEquals('foo@bar.com', $this->tag('{{ users group="favourite|non_favourite" }}{{ email }}{{ /users }}'));
        $this->assertEquals('', $this->tag('{{ users group="non_favourite|another_non_favourite" }}{{ email }}{{ /users }}'));
    }

    #[Test]
    public function it_only_shows_users_in_roles()
    {
        $this->setTestRoles([
            'webmaster' => ['super'],
            'not_webmaster' => [],
            'another_not_webmaster' => [],
        ]);

        $this->setTestUserGroups([
            'favourite' => ['webmaster'], // Though super users have permission to do everything, they do not inherit all groups
            'non_favourite',
        ]);

        $user = User::make()->email('foo@bar.com')->assignRole('webmaster')->save();

        $this->assertEquals('foo@bar.com', $this->tag('{{ users role="webmaster" }}{{ email }}{{ /users }}'));
        $this->assertEquals('', $this->tag('{{ users group="not_webmaster" }}{{ email }}{{ /users }}'));

        $this->assertEquals('foo@bar.com', $this->tag('{{ users role="webmaster|not_webmaster" }}{{ email }}{{ /users }}'));
        $this->assertEquals('', $this->tag('{{ users group="not_webmaster|another_not_webmaster" }}{{ email }}{{ /users }}'));
    }
}
