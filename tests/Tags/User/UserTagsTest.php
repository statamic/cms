<?php

namespace Tests\Tags\User;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\FakesUserGroups;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserTagsTest extends TestCase
{
    use FakesRoles,
        FakesUserGroups,
        PreventSavingStacheItemsToDisk;

    private function tag($tag, $params = [])
    {
        return Parse::template($tag, $params);
    }

    #[Test]
    public function it_renders_user_can_tag_content()
    {
        $this->setTestRoles([
            'webmaster' => ['super'],
            'admin' => ['access cp', 'configure collections'],
            'author' => ['access cp'],
        ]);

        $this->actingAs(User::make()->assignRole('webmaster')->save());

        $this->assertEquals('yes', $this->tag('{{ user:can do="configure collections" }}yes{{ /user:can }}'));
        $this->assertEquals('', $this->tag('{{ user:cant do="configure collections" }}yes{{ /user:cant }}'));

        $this->actingAs(User::make()->assignRole('admin')->save());

        $this->assertEquals('yes', $this->tag('{{ user:can do="configure collections" }}yes{{ /user:can }}'));
        $this->assertEquals('', $this->tag('{{ user:cant do="configure collections" }}yes{{ /user:cant }}'));

        $this->actingAs(User::make()->assignRole('author')->save());

        $this->assertEquals('', $this->tag('{{ user:can do="configure collections" }}yes{{ /user:can }}'));
        $this->assertEquals('yes', $this->tag('{{ user:cant do="configure collections" }}yes{{ /user:cant }}'));

        // Test if user has any of these permissions
        $this->assertEquals('yes', $this->tag('{{ user:can do="access cp|configure collections" }}yes{{ /user:can }}'));
        $this->assertEquals('', $this->tag('{{ user:cant do="access cp|configure collections" }}yes{{ /user:cant }}'));
    }

    #[Test]
    public function it_renders_user_can_with_aurguments_tag_content()
    {
        $this->actingAs(User::make()->save());

        Gate::define('test gate', function ($user, $value) {
            return $value === 'foo';
        });

        $this->assertEquals('yes', $this->tag('{{ user:can do="test gate" value="foo" }}yes{{ /user:can }}'));
        $this->assertEquals('', $this->tag('{{ user:cant do="test gate" value="foo" }}yes{{ /user:cant }}'));

        $this->assertEquals('', $this->tag('{{ user:can do="test gate" value="bar" }}yes{{ /user:can }}'));
        $this->assertEquals('yes', $this->tag('{{ user:cant do="test gate" value="bar" }}yes{{ /user:cant }}'));
    }

    #[Test]
    public function it_renders_user_is_tag_content()
    {
        $this->setTestRoles([
            'webmaster' => ['super'], // Though super users have permission to do everything, they do not inherit all roles
            'admin',
        ]);

        $this->actingAs(User::make()->assignRole('webmaster')->save());

        $this->assertEquals('yes', $this->tag('{{ user:is role="webmaster" }}yes{{ /user:is }}'));
        $this->assertEquals('', $this->tag('{{ user:is role="admin" }}yes{{ /user:is }}'));
        $this->assertEquals('', $this->tag('{{ user:isnt role="webmaster" }}yes{{ /user:isnt }}'));
        $this->assertEquals('yes', $this->tag('{{ user:isnt role="admin" }}yes{{ /user:isnt }}'));

        // Test if user is assigned any of these roles
        $this->assertEquals('yes', $this->tag('{{ user:is role="webmaster|admin" }}yes{{ /user:is }}'));
        $this->assertEquals('', $this->tag('{{ user:isnt role="webmaster|admin" }}yes{{ /user:isnt }}'));

        // test if it handles the value of a user_roles tag
        $this->assertEquals('yes', $this->tag('{{ user:is :roles="roles" }}yes{{ /user:is }}', ['roles' => Role::all()]));
        $this->assertEquals('', $this->tag('{{ user:isnt :roles="roles" }}yes{{ /user:isnt }}', ['roles' => Role::all()]));
    }

    #[Test]
    public function it_renders_user_in_tag_content()
    {
        $this->setTestRoles([
            'webmaster' => ['super'],
        ]);

        $this->setTestUserGroups([
            'favourite' => ['webmaster'], // Though super users have permission to do everything, they do not inherit all groups
            'non_favourite',
        ]);

        $this->actingAs(User::make()->addToGroup('favourite')->save());

        $this->assertEquals('yes', $this->tag('{{ user:in group="favourite" }}yes{{ /user:in }}'));
        $this->assertEquals('', $this->tag('{{ user:in group="non_favourite" }}yes{{ /user:in }}'));
        $this->assertEquals('', $this->tag('{{ user:not_in group="favourite" }}yes{{ /user:not_in }}'));
        $this->assertEquals('yes', $this->tag('{{ user:not_in group="non_favourite" }}yes{{ /user:not_in }}'));

        // Test if user is in any of these groups
        $this->assertEquals('yes', $this->tag('{{ user:in group="favourite|non_favourite" }}yes{{ /user:in }}'));
        $this->assertEquals('', $this->tag('{{ user:not_in group="favourite|non_favourite" }}yes{{ /user:not_in }}'));
    }

    #[Test]
    public function it_can_logout_user()
    {
        $this->actingAs(User::make()->save());

        $this->assertTrue(auth()->check());

        try {
            $this->tag('{{ user:logout }}');
        } catch (HttpResponseException $exception) {
            //
        }

        $this->assertFalse(auth()->check());
        $this->assertEquals(url('/'), $exception->getResponse()->getTargetUrl());
    }

    #[Test]
    public function it_can_logout_user_with_custom_redirect()
    {
        $this->actingAs(User::make()->save());

        $this->assertTrue(auth()->check());

        try {
            $this->tag('{{ user:logout redirect="home" }}');
        } catch (HttpResponseException $exception) {
            //
        }

        $this->assertFalse(auth()->check());
        $this->assertEquals(url('home'), $exception->getResponse()->getTargetUrl());
    }

    #[Test]
    public function it_can_render_logout_url()
    {
        $this->assertEquals(route('statamic.logout'), $this->tag('{{ user:logout_url }}'));

        $this->assertEquals(route('statamic.logout', ['redirect' => 'home']), $this->tag('{{ user:logout_url redirect="home" }}'));
    }

    #[Test]
    public function it_can_load_user_by_email()
    {
        User::make()->email('foo@bar.com')->save();

        $this->assertEquals('foo@bar.com', $this->tag('{{ user email="foo@bar.com" }}{{email}}{{ /user }}'));
    }

    #[Test]
    public function it_can_load_user_by_field()
    {
        User::make()
            ->email('foo@bar.com')
            ->data(['field1' => 'foobar'])
            ->save();

        $this->assertEquals('foo@bar.com', $this->tag('{{ user field="field1" value="foobar" }}{{email}}{{ /user }}'));
    }
}
