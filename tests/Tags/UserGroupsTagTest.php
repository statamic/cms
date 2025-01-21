<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Facades\UserGroup;
use Tests\TestCase;

class UserGroupsTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        UserGroup::all()->each->delete();
    }

    #[Test]
    public function it_outputs_no_results()
    {
        $this->assertEquals('nothing', $this->tag('{{ user_groups }}{{ if no_results }}nothing{{ else }}something{{ /if }}{{ /user_groups }}'));
    }

    #[Test]
    public function it_gets_all_groups()
    {
        UserGroup::make()->handle('test')->title('Test')->save();
        UserGroup::make()->handle('test2')->title('Test 2')->save();
        UserGroup::make()->handle('test3')->title('Test 3')->save();

        $this->assertEquals('test|test2|test3|', $this->tag('{{ user_groups }}{{ handle }}|{{ /user_groups }}'));
    }

    #[Test]
    public function it_gets_a_group()
    {
        UserGroup::make()->handle('test')->title('Test')->save();
        UserGroup::make()->handle('test2')->title('Test 2')->save();
        UserGroup::make()->handle('test3')->title('Test 3')->save();

        $this->assertEquals('test2', $this->tag('{{ user_groups handle="test2" }}{{ handle }}{{ /user_groups }}'));
    }

    #[Test]
    public function it_gets_multiple_groups()
    {
        UserGroup::make()->handle('test')->title('Test')->save();
        UserGroup::make()->handle('test2')->title('Test 2')->save();
        UserGroup::make()->handle('test3')->title('Test 3')->save();

        $this->assertEquals('test2|test3|', $this->tag('{{ user_groups handle="test2|test3" }}{{ handle }}|{{ /user_groups }}'));
        $this->assertEquals('test2|test3|', $this->tag('{{ user_groups :handle="groups" }}{{ handle }}|{{ /user_groups }}', ['groups' => ['test2', 'test3']]));
    }

    #[Test]
    public function it_outputs_no_results_when_finding_multiple_groups()
    {
        $this->assertEquals('nothing', $this->tag('{{ user_groups handle="test2|test3" }}{{ if no_results }}nothing{{ else }}something{{ /if }}{{ /user_groups }}'));
        $this->assertEquals('nothing', $this->tag('{{ user_groups :handle="groups" }}{{ if no_results }}nothing{{ else }}something{{ /if }}{{ /user_groups }}', ['groups' => ['test2', 'test3']]));
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }
}
