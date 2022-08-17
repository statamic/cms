<?php

namespace Tests\Tags;

use Illuminate\Support\Facades\File;
use Statamic\Facades\Parse;
use Tests\TestCase;

class SvgTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        File::copy(__DIR__.'/../../resources/svg/users.svg', resource_path('users.svg'));
    }

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    /** @test */
    public function it_renders_svg()
    {
        $this->assertStringStartsWith('<svg viewBox=', $this->tag('{{ svg:users }}'));
        $this->assertStringStartsWith('<svg viewBox=', $this->tag('{{ svg src="users" }}'));
    }

    /** @test */
    public function it_renders_svg_with_additional_params()
    {
        $this->assertStringStartsWith('<svg class="mb-1" viewBox=', $this->tag('{{ svg src="users" class="mb-1" }}'));
    }
}
