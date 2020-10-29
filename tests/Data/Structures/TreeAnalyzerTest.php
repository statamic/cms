<?php

namespace Tests\Data\Structures;

use Statamic\Structures\TreeAnalyzer;
use Tests\TestCase;

class TreeAnalyzerTest extends TestCase
{
    /** @test */
    public function it_sees_no_changes()
    {
        $old = [];
        $new = [];

        $analyzer = (new TreeAnalyzer)->analyze($old, $new);

        $this->assertFalse($analyzer->hasChanged());
        $this->assertEquals([], $analyzer->affected());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals([], $analyzer->moved());
    }

    /** @test */
    public function it_sees_additions()
    {
        $old = [];
        $new = [
            ['entry' => '1', 'a' => 'b'],
        ];

        $analyzer = (new TreeAnalyzer)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['1'], $analyzer->affected());
        $this->assertEquals(['1'], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals([], $analyzer->moved());
    }

    /** @test */
    public function it_sees_removals()
    {
        $old = [
            ['entry' => '1', 'a' => 'b'],
        ];
        $new = [];

        $analyzer = (new TreeAnalyzer)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['1'], $analyzer->affected());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals(['1'], $analyzer->removed());
        $this->assertEquals([], $analyzer->moved());
    }

    /** @test */
    public function it_sees_moves()
    {
        $old = [
            ['entry' => '1', 'a' => 'b'],
            ['entry' => '2', 'c' => 'd'],
        ];
        $new = [
            ['entry' => '2', 'c' => 'd'],
            ['entry' => '1', 'a' => 'b'],
        ];

        $analyzer = (new TreeAnalyzer)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['1', '2'], $analyzer->affected());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals(['1', '2'], $analyzer->moved());
    }

    /** @test */
    public function it_sees_additions_and_removals()
    {
        $old = [
            ['entry' => '1', 'a' => 'b'],
        ];
        $new = [
            ['entry' => '2', 'c' => 'd'],
        ];

        $analyzer = (new TreeAnalyzer)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['1', '2'], $analyzer->affected());
        $this->assertEquals(['2'], $analyzer->added());
        $this->assertEquals(['1'], $analyzer->removed());
    }

    /** @test */
    public function it_sees_multilevel_changes()
    {
        $old = [
            ['entry' => '1', 'a' => 'b', 'children' => [
                ['entry' => '7', 'c' => 'd'],
                ['entry' => '8', 'e' => 'f'],
                ['entry' => '10', 'g' => 'h'],
            ]],
            ['entry' => '2', 'i' => 'j', 'children' => [
                ['entry' => '3', 'k' => 'l'],
                ['entry' => '13', 'm' => 'n'],
            ]],
        ];

        $new = [
            ['entry' => '1', 'a' => 'b'],
            ['entry' => '2', 'i' => 'j', 'children' => [
                ['entry' => '3', 'k' => 'l'],
                ['entry' => '13', 'm' => 'n'],
                ['entry' => '10', 'g' => 'h'],
            ]],
            ['entry' => '9', 'o' => 'p'],
        ];

        $analyzer = (new TreeAnalyzer)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['9'], $analyzer->added());
        $this->assertEquals(['7', '8'], $analyzer->removed());
        $this->assertEquals(['10'], $analyzer->moved());
        $this->assertEquals(['7', '8', '9', '10'], $analyzer->affected());
    }
}
