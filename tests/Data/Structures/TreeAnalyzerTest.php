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
            ['entry' => '1'],
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
            ['entry' => '1'],
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
            ['entry' => '1'],
            ['entry' => '2'],
        ];
        $new = [
            ['entry' => '2'],
            ['entry' => '1'],
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
            ['entry' => '1'],
        ];
        $new = [
            ['entry' => '2'],
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
            ['entry' => '1', 'children' => [
                ['entry' => '7'],
                ['entry' => '8'],
                ['entry' => '10'],
            ]],
            ['entry' => '2', 'children' => [
                ['entry' => '3'],
                ['entry' => '13'],
            ]],
        ];

        $new = [
            ['entry' => '1'],
            ['entry' => '2', 'children' => [
                ['entry' => '3'],
                ['entry' => '13'],
                ['entry' => '10'],
            ]],
            ['entry' => '9'],
        ];

        $analyzer = (new TreeAnalyzer)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['9'], $analyzer->added());
        $this->assertEquals(['7', '8'], $analyzer->removed());
        $this->assertEquals(['10'], $analyzer->moved());
        $this->assertEquals(['7', '8', '9', '10'], $analyzer->affected());
    }
}
