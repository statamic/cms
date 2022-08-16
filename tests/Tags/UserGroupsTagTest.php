<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
use Statamic\Facades\UserGroup;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserGroupsTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_all_groups()
    {
        UserGroup::make()->handle('test')->title('Test')->save();
        UserGroup::make()->handle('test2')->title('Test 2')->save();
        UserGroup::make()->handle('test3')->title('Test 3')->save();

        $this->assertEquals('test|test2|test3|', $this->tag('{{ user_groups }}{{ handle }}|{{ /user_groups }}'));
    }

    /** @test */
    public function it_gets_a_group()
    {
        UserGroup::make()->handle('test')->title('Test')->save();
        UserGroup::make()->handle('test2')->title('Test 2')->save();
        UserGroup::make()->handle('test3')->title('Test 3')->save();

        $this->assertEquals('test2', $this->tag('{{ user_groups handle="test2" }}{{ handle }}{{ /user_groups }}'));
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }
}
