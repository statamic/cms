<?php

namespace Tests\Data\Structures;

use Mockery;
use Statamic\Entries\Entry;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Value;
use Statamic\Structures\AugmentedPage;
use Statamic\Structures\Page;
use Tests\Data\AugmentedTestCase;

class AugmentedPageTest extends AugmentedTestCase
{
    /** @test */
    public function it_gets_page_keys()
    {
        $page = Mockery::mock(Page::class);
        $page->shouldReceive('reference')->andReturnFalse();

        $augmented = new AugmentedPage($page);

        $expected = [
            'title',
            'url',
            'uri',
            'permalink',
        ];

        $actual = $augmented->keys();

        $this->assertEquals(
            collect($expected)->sort()->values()->all(),
            collect($actual)->sort()->values()->all(),
        );
    }

    /** @test */
    public function it_gets_entry_keys()
    {
        $blueprint = Blueprint::makeFromFields([
            'title' => ['type' => 'text'],
            'foo' => ['type' => 'text'],
            'one' => ['type' => 'text'],
        ])->setHandle('test');

        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('values')->andReturn(collect([
            'one' => 'two',
            'three' => 'four',
        ]));
        $entry->shouldReceive('supplements')->andReturn(collect([
            'alfa' => 'bravo',
            'charlie' => 'delta',
        ]));
        $entry->shouldReceive('blueprint')->andReturn($blueprint);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('reference')->andReturn('123');
        $page->shouldReceive('referenceExists')->andReturnTrue();
        $page->shouldReceive('entry')->andReturn($entry);

        $augmented = new AugmentedPage($page);

        $expected = [
            // entry values
            'one', 'three',
            // entry supplements
            'alfa', 'charlie',
            // entry blueprint
            'title', 'foo',
            // augmented entry keys
            'amp_url', 'api_url', 'collection', 'date', 'edit_url', 'id', 'is_entry',
            'last_modified', 'locale', 'mount', 'order', 'permalink', 'private',
            'published', 'slug', 'status', 'updated_at', 'updated_by', 'uri', 'url',
        ];

        $actual = $augmented->keys();

        $this->assertEquals(
            collect($expected)->sort()->values()->all(),
            collect($actual)->sort()->values()->all(),
        );
    }

    /** @test */
    public function it_gets_values_from_the_page()
    {
        $page = Mockery::mock(Page::class);
        $page->shouldReceive('reference')->andReturnFalse();
        $page->shouldReceive('title')->andReturn('The Page Title');
        $page->shouldReceive('blueprint')->andReturnNull();
        $page->shouldReceive('url')->andReturn('/the-url');
        $page->shouldReceive('uri')->andReturn('/the-uri');
        $page->shouldReceive('absoluteUrl')->andReturn('https://site.com/the-permalink');

        $augmented = new AugmentedPage($page);

        $expectations = [
            'title' => ['type' => 'string', 'value' => 'The Page Title'],
            'url' => ['type' => 'string', 'value' => '/the-url'],
            'uri' => ['type' => 'string', 'value' => '/the-uri'],
            'permalink' => ['type' => 'string', 'value' => 'https://site.com/the-permalink'],
        ];

        $this->assertAugmentedCorrectly($expectations, $augmented);
    }

    /** @test */
    public function it_gets_values_from_the_entry()
    {
        $blueprint = Blueprint::makeFromFields([
            'title' => ['type' => 'text'],
        ])->setHandle('test');

        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('values')->andReturn(collect(['title' => 'The Entry Title']));
        $entry->shouldReceive('supplements')->andReturn(collect());
        $entry->shouldReceive('value')->with('title')->andReturn('The Entry Title');
        $entry->shouldReceive('getSupplement')->with('title')->andReturnNull();
        $entry->shouldReceive('blueprint')->andReturn($blueprint);
        $entry->shouldReceive('url')->andReturn('/the-url');
        $entry->shouldReceive('uri')->andReturn('/the-uri');
        $entry->shouldReceive('absoluteUrl')->andReturn('https://site.com/the-permalink');

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('reference')->andReturn('123');
        $page->shouldReceive('referenceExists')->andReturnTrue();
        $page->shouldReceive('entry')->andReturn($entry);

        $augmented = new AugmentedPage($page);

        $expectations = [
            'title' => ['type' => Value::class, 'value' => 'The Entry Title'],
            'url' => ['type' => 'string', 'value' => '/the-url'],
            'uri' => ['type' => 'string', 'value' => '/the-uri'],
            'permalink' => ['type' => 'string', 'value' => 'https://site.com/the-permalink'],
        ];

        $this->assertSubsetAugmentedCorrectly($expectations, $augmented);
    }
}
