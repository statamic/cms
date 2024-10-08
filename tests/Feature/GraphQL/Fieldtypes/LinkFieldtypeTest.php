<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Entry;

#[Group('graphql')]
class LinkFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
    public function it_gets_null_when_undefined()
    {
        $entry = $this->createEntryWithFields([
            'link' => [
                'value' => null,
                'field' => ['type' => 'link'],
            ],
        ]);

        ResolveRedirect::shouldReceive('item')->never();

        $this->assertGqlEntryHas('link', ['link' => null]);
    }

    #[Test]
    public function it_gets_a_hardcoded_url()
    {
        $entry = $this->createEntryWithFields([
            'link' => [
                'value' => '/hardcoded',
                'field' => ['type' => 'link'],
            ],
        ]);

        ResolveRedirect::shouldReceive('item')->once()->with('/hardcoded', $entry, true)->andReturn('/hardcoded');

        $this->assertGqlEntryHas('link', ['link' => '/hardcoded']);
    }

    #[Test]
    public function it_gets_an_entry()
    {
        $entry = $this->createEntryWithFields([
            'link' => [
                'value' => 'entry::123',
                'field' => ['type' => 'link'],
            ],
        ]);

        $another = Mockery::mock(Entry::class);
        $another->shouldReceive('url')->once()->andReturn('/the-entry-url');

        ResolveRedirect::shouldReceive('item')->once()->with('entry::123', $entry, true)->andReturn($another);

        $this->assertGqlEntryHas('link', ['link' => '/the-entry-url']);
    }

    #[Test]
    public function it_gets_a_child_url()
    {
        $entry = $this->createEntryWithFields([
            'link' => [
                'value' => '@child',
                'field' => ['type' => 'link'],
            ],
        ]);

        $another = Mockery::mock(Entry::class);
        $another->shouldReceive('url')->once()->andReturn('/the-first-child');

        ResolveRedirect::shouldReceive('item')->once()->with('@child', $entry, true)->andReturn($another);

        $this->assertGqlEntryHas('link', ['link' => '/the-first-child']);
    }

    #[Test]
    public function it_gets_a_404()
    {
        $entry = $this->createEntryWithFields([
            'link' => [
                'value' => 'entry::unknown',
                'field' => ['type' => 'link'],
            ],
        ]);

        ResolveRedirect::shouldReceive('item')->once()->with('entry::unknown', $entry, true)->andReturnNull();

        $this->assertGqlEntryHas('link', ['link' => null]);
    }
}
