<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;

/** @group graphql */
class LinkFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_null_when_undefined()
    {
        $entry = $this->createEntryWithFields([
            'link' => [
                'value' => null,
                'field' => ['type' => 'link'],
            ],
        ]);

        ResolveRedirect::shouldReceive('resolve')->once()->withSomeOfArgs(null)->andReturnNull();

        $this->assertGqlEntryHas('link', ['link' => null]);
    }

    /** @test */
    public function it_gets_a_hardcoded_url()
    {
        $entry = $this->createEntryWithFields([
            'link' => [
                'value' => '/hardcoded',
                'field' => ['type' => 'link'],
            ],
        ]);

        ResolveRedirect::shouldReceive('resolve')->once()->withSomeOfArgs('/hardcoded')->andReturn('/hardcoded');

        $this->assertGqlEntryHas('link', ['link' => '/hardcoded']);
    }

    /** @test */
    public function it_gets_an_entry()
    {
        $entry = $this->createEntryWithFields([
            'link' => [
                'value' => 'entry::123',
                'field' => ['type' => 'link'],
            ],
        ]);

        ResolveRedirect::shouldReceive('resolve')->once()->withSomeOfArgs('entry::123')->andReturn('/the-entry-url');

        $this->assertGqlEntryHas('link', ['link' => '/the-entry-url']);
    }

    /** @test */
    public function it_gets_a_child_url()
    {
        $entry = $this->createEntryWithFields([
            'link' => [
                'value' => '@child',
                'field' => ['type' => 'link'],
            ],
        ]);

        ResolveRedirect::shouldReceive('resolve')->once()->withSomeOfArgs('@child')->andReturn('/the-first-child');

        $this->assertGqlEntryHas('link', ['link' => '/the-first-child']);
    }

    /** @test */
    public function it_gets_a_404()
    {
        $entry = $this->createEntryWithFields([
            'link' => [
                'value' => 'entry::unknown',
                'field' => ['type' => 'link'],
            ],
        ]);

        ResolveRedirect::shouldReceive('resolve')->once()->withSomeOfArgs('entry::unknown')->andReturn(404);

        $this->assertGqlEntryHas('link', ['link' => null]);
    }
}
