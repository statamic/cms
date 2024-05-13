<?php

namespace Tests\Fieldtypes;

use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TemplatesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('view.paths', [
            __DIR__.'/../__fixtures__/templates',
        ]);
    }

    /** @test */
    public function it_returns_a_list_of_templates()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('api.templates.index'))
            ->assertJson([
                'blog/index',
                'conditions-literals',
                'five_hundred_nested_ifs',
                'nested-conditionals',
            ]);
    }
}
