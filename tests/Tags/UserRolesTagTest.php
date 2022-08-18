<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
use Statamic\Facades\Role;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserRolesTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_all_groups()
    {
        Role::make()->handle('test')->title('Test')->save();
        Role::make()->handle('test2')->title('Test 2')->save();
        Role::make()->handle('test3')->title('Test 3')->save();

        $this->assertEquals('test|test2|test3|', $this->tag('{{ user_roles }}{{ handle }}|{{ /user_roles }}'));
    }

    /** @test */
    public function it_gets_a_group()
    {
        Role::make()->handle('test')->title('Test')->save();
        Role::make()->handle('test2')->title('Test 2')->save();
        Role::make()->handle('test3')->title('Test 3')->save();

        $this->assertEquals('test2', $this->tag('{{ user_roles handle="test2" }}{{ handle }}{{ /user_roles }}'));
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }
}
