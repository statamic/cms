<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;
use Facades\Tests\Factories\EntryFactory;

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

        $this->assertGqlEntryHas('link', ['link' => '/hardcoded']);
    }

    /** @test */
    public function it_gets_an_entry()
    {
        EntryFactory::collection('test')->id(2)->slug('the-entry-url')->create();

        $entry = $this->createEntryWithFields([
            'link' => [
                'value' => 'entry::2',
                'field' => ['type' => 'link'],
            ],
        ]);

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

        ResolveRedirect::shouldReceive('resolve')->once()->with('@child', $entry, true)->andReturn('/the-first-child');

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

        $this->assertGqlEntryHas('link', ['link' => null]);
    }
}
