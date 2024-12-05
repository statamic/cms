<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Facades\Role;
use Tests\TestCase;

class UserRolesTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Role::all()->each->delete();
    }

    #[Test]
    public function it_outputs_no_results()
    {
        $this->assertEquals('nothing', $this->tag('{{ user_roles }}{{ if no_results }}nothing{{ else }}something{{ /if }}{{ /user_roles }}'));
    }

    #[Test]
    public function it_gets_all_roles()
    {
        Role::make()->handle('test')->title('Test')->save();
        Role::make()->handle('test2')->title('Test 2')->save();
        Role::make()->handle('test3')->title('Test 3')->save();

        $this->assertEquals('test|test2|test3|', $this->tag('{{ user_roles }}{{ handle }}|{{ /user_roles }}'));
    }

    #[Test]
    public function it_gets_a_role()
    {
        Role::make()->handle('test')->title('Test')->save();
        Role::make()->handle('test2')->title('Test 2')->save();
        Role::make()->handle('test3')->title('Test 3')->save();

        $this->assertEquals('test2', $this->tag('{{ user_roles handle="test2" }}{{ handle }}{{ /user_roles }}'));
    }

    #[Test]
    public function it_gets_multiple_roles()
    {
        Role::make()->handle('test')->title('Test')->save();
        Role::make()->handle('test2')->title('Test 2')->save();
        Role::make()->handle('test3')->title('Test 3')->save();

        $this->assertEquals('test2|test3|', $this->tag('{{ user_roles handle="test2|test3" }}{{ handle }}|{{ /user_roles }}'));
        $this->assertEquals('test2|test3|', $this->tag('{{ user_roles :handle="roles" }}{{ handle }}|{{ /user_roles }}', ['roles' => ['test2', 'test3']]));
    }

    #[Test]
    public function it_outputs_no_results_when_finding_multiple_roles()
    {
        $this->assertEquals('nothing', $this->tag('{{ user_roles handle="test2|test3" }}{{ if no_results }}nothing{{ else }}something{{ /if }}{{ /user_roles }}'));
        $this->assertEquals('nothing', $this->tag('{{ user_roles :handle="roles" }}{{ if no_results }}nothing{{ else }}something{{ /if }}{{ /user_roles }}', ['roles' => ['test2', 'test3']]));
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }
}
