<?php

namespace Tests\Data\Structures;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Facades\Blueprint;
use Statamic\Structures\AugmentedPage;
use Statamic\Structures\Page;
use Tests\Data\AugmentedTestCase;

class AugmentedPageTest extends AugmentedTestCase
{
    #[Test]
    public function it_gets_page_keys()
    {
        $page = Mockery::mock(Page::class);
        $page->shouldReceive('reference')->andReturnFalse();
        $page->shouldReceive('data')->andReturn(collect(['one' => 'two', 'three' => 'four']));
        $page->shouldReceive('supplements')->andReturn(collect(['five' => 'six']));

        $augmented = new AugmentedPage($page);

        $expected = [
            'id',
            'entry_id',
            'title',
            'url',
            'uri',
            'permalink',
            'one',
            'three',
            'five',
        ];

        $actual = $augmented->keys();

        $this->assertEquals(
            collect($expected)->sort()->values()->all(),
            collect($actual)->sort()->values()->all()
        );
    }

    #[Test]
    public function it_gets_entry_keys()
    {
        $entryBlueprint = Blueprint::makeFromFields([
            'title' => ['type' => 'text'],
            'foo' => ['type' => 'text'],
            'one' => ['type' => 'text'],
        ])->setNamespace('collections.articles')->setHandle('article');

        $pageBlueprint = Blueprint::makeFromFields([
            'jane' => ['type' => 'text'],
        ])->setNamespace('navs')->setHandle('pages');

        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('keys')->andReturn(collect([
            'one',
            'three',
        ]));
        $entry->shouldReceive('supplements')->andReturn(collect([
            'alfa' => 'bravo',
            'charlie' => 'delta',
        ]));
        $entry->shouldReceive('blueprint')->andReturn($entryBlueprint);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('reference')->andReturn('123');
        $page->shouldReceive('referenceExists')->andReturnTrue();
        $page->shouldReceive('entry')->andReturn($entry);
        $page->shouldReceive('blueprint')->andReturn($pageBlueprint);
        $page->shouldReceive('data')->andReturn(collect([
            'john' => 'doe',
            'jane' => 'doe',
            'three' => 'four',
        ]));
        $page->shouldReceive('supplements')->andReturn(collect([
            'echo' => 'foxtrot',
            'golf' => 'hotel',
        ]));

        $augmented = new AugmentedPage($page);

        $expected = [
            // entry values
            'one', 'three',
            // entry supplements
            'alfa', 'charlie',
            // entry blueprint
            'title', 'foo',
            // augmented entry keys
            'api_url', 'collection', 'blueprint', 'date', 'edit_url', 'id', 'origin_id', 'is_entry',
            'last_modified', 'locale', 'mount', 'order', 'permalink', 'private',
            'published', 'slug', 'status', 'updated_at', 'updated_by', 'uri', 'url',
            // page blueprint
            'jane',
            // page data
            'john',
            // page augmente
            'echo', 'golf',
            // page keys
            'entry_id',
        ];

        $actual = $augmented->keys();

        $this->assertEquals(
            collect($expected)->sort()->values()->all(),
            collect($actual)->sort()->values()->all()
        );
    }

    #[Test]
    public function it_gets_values_from_the_page()
    {
        $blueprint = Blueprint::makeFromFields([
            'one' => ['type' => 'text'],
            'three' => ['type' => 'text'],
        ]);

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('id')->andReturn('page-id');
        $page->shouldReceive('reference')->andReturnNull();
        $page->shouldReceive('title')->andReturn('The Page Title');
        $page->shouldReceive('blueprint')->andReturn($blueprint);
        $page->shouldReceive('url')->andReturn('/the-url');
        $page->shouldReceive('uri')->andReturn('/the-uri');
        $page->shouldReceive('absoluteUrl')->andReturn('https://site.com/the-permalink');
        $page->shouldReceive('data')->andReturn(collect(['one' => 'two', 'three' => 'four', 'five' => 'six']));
        $page->shouldReceive('supplements')->andReturn(collect(['seven' => 'eight']));
        $page->shouldReceive('value')->with('one')->andReturn('two');
        $page->shouldReceive('value')->with('three')->andReturn('four');
        $page->shouldReceive('value')->with('five')->andReturn('six');
        $page->shouldReceive('getSupplement')->with('one')->andReturnNull();
        $page->shouldReceive('getSupplement')->with('three')->andReturnNull();
        $page->shouldReceive('getSupplement')->with('five')->andReturnNull();
        $page->shouldReceive('getSupplement')->with('seven')->andReturn('eight');

        $augmented = new AugmentedPage($page);

        $expectations = [
            'title' => ['type' => 'string', 'value' => 'The Page Title'],
            'url' => ['type' => 'string', 'value' => '/the-url'],
            'uri' => ['type' => 'string', 'value' => '/the-uri'],
            'permalink' => ['type' => 'string', 'value' => 'https://site.com/the-permalink'],
            'one' => ['type' => 'string', 'value' => 'two'],
            'three' => ['type' => 'string', 'value' => 'four'],
            'five' => ['type' => 'string', 'value' => 'six'],
            'seven' => ['type' => 'string', 'value' => 'eight'],
            'id' => ['type' => 'string', 'value' => 'page-id'],
            'entry_id' => ['type' => 'string', 'value' => null],
        ];

        $this->assertAugmentedCorrectly($expectations, $augmented);
    }

    #[Test]
    public function it_gets_values_from_the_entry()
    {
        $entryBlueprint = Blueprint::makeFromFields([
            'title' => ['type' => 'text'],
            'one' => ['type' => 'text'],
        ])->setNamespace('collections.articles')->setHandle('article');

        $pageBlueprint = Blueprint::makeFromFields([
            'one' => ['type' => 'textarea'],
            'three' => ['type' => 'textarea'],
        ])->setNamespace('navs')->setHandle('pages');

        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('keys')->andReturn(collect([
            'title',
            'one',
            'three',
            'five',
        ]));
        $entry->shouldReceive('supplements')->andReturn(collect());
        $entry->shouldReceive('value')->with('title')->andReturn('The Entry Title');
        $entry->shouldReceive('value')->with('one')->andReturn('two');
        $entry->shouldReceive('value')->with('three')->andReturnNull('four');
        $entry->shouldReceive('value')->with('five')->andReturn('six');
        $entry->shouldReceive('getSupplement')->with('title')->andReturnNull();
        $entry->shouldReceive('getSupplement')->with('one')->andReturnNull();
        $entry->shouldReceive('getSupplement')->with('three')->andReturnNull();
        $entry->shouldReceive('getSupplement')->with('five')->andReturnNull();
        $entry->shouldReceive('blueprint')->andReturn($entryBlueprint);
        $entry->shouldReceive('url')->andReturn('/the-url');
        $entry->shouldReceive('uri')->andReturn('/the-uri');
        $entry->shouldReceive('id')->andReturn('123');
        $entry->shouldReceive('absoluteUrl')->andReturn('https://site.com/the-permalink');

        $page = Mockery::mock(Page::class);
        $page->shouldReceive('id')->andReturn('page-id');
        $page->shouldReceive('reference')->andReturn('123');
        $page->shouldReceive('referenceExists')->andReturnTrue();
        $page->shouldReceive('entry')->andReturn($entry);
        $page->shouldReceive('blueprint')->andReturn($pageBlueprint);
        $page->shouldReceive('data')->andReturn(collect(['one' => 'dos', 'three' => 'quatro', 'five' => 'seis']));
        $page->shouldReceive('supplements')->andReturn(collect(['seven' => 'ocho']));
        $page->shouldReceive('title')->andReturn('The Page Title');
        $page->shouldReceive('value')->with('one')->andReturn('dos');
        $page->shouldReceive('value')->with('three')->andReturn('quatro');
        $page->shouldReceive('value')->with('five')->andReturn('seis');
        $page->shouldReceive('getSupplement')->with('one')->andReturnNull();
        $page->shouldReceive('getSupplement')->with('three')->andReturnNull();
        $page->shouldReceive('getSupplement')->with('five')->andReturnNull();
        $page->shouldReceive('getSupplement')->with('seven')->andReturn('ocho');

        $augmented = new AugmentedPage($page);

        $expectations = [
            'title' => ['type' => 'string', 'value' => 'The Page Title'],
            'url' => ['type' => 'string', 'value' => '/the-url'],
            'uri' => ['type' => 'string', 'value' => '/the-uri'],
            'permalink' => ['type' => 'string', 'value' => 'https://site.com/the-permalink'],
            'one' => ['type' => 'string', 'value' => 'dos', 'fieldtype' => 'textarea'], // assert fieldtype to ensure the field from the page blueprint wins
            'three' => ['type' => 'string', 'value' => 'quatro'],
            'five' => ['type' => 'string', 'value' => 'seis'],
            'seven' => ['type' => 'string', 'value' => 'ocho'],
            'id' => ['type' => 'string', 'value' => 'page-id'],
            'entry_id' => ['type' => 'string', 'value' => '123'],
        ];

        $this->assertSubsetAugmentedCorrectly($expectations, $augmented);
    }
}
