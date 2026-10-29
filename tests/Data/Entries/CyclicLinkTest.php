<?php

namespace Tests\Data\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Fieldtypes\Link;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CyclicLinkTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        CountingLink::$augmentations = 0;
        CountingLink::register();

        $blueprint = Blueprint::makeFromFields(['link' => ['type' => 'counting_link']]);
        BlueprintRepository::shouldReceive('in')->with('collections/pages')->andReturn(collect(['pages' => $blueprint]));

        Collection::make('pages')->routes('{slug}')->save();
    }

    private function page(string $id, ?string $linksTo = null, array $data = [])
    {
        return EntryFactory::id($id)
            ->collection('pages')
            ->slug($id)
            ->data(array_merge(['title' => strtoupper($id), 'link' => $linksTo ? "entry::$linksTo" : null], $data))
            ->create();
    }

    #[Test]
    public function it_converts_entries_that_link_to_each_other_to_an_array()
    {
        $a = $this->page('a', 'b');
        $this->page('b', 'a');

        $array = $a->toArray();

        $this->assertSame('B', $array['link']['title']);
        $this->assertSame('A', $array['link']['link']['title']);

        // A -> B -> A is expanded in full, but the link back to B is shallow.
        $this->assertSame('B', $array['link']['link']['link']['title']);
        $this->assertArrayNotHasKey('link', $array['link']['link']['link']);
    }

    #[Test]
    public function it_converts_an_entry_that_links_to_itself_to_an_array()
    {
        $a = $this->page('a', 'a');

        $array = $a->toArray();

        $this->assertSame('A', $array['link']['title']);
    }

    #[Test]
    public function it_converts_a_longer_cycle_to_an_array()
    {
        $a = $this->page('a', 'b');
        $this->page('b', 'c');
        $this->page('c', 'a');

        $array = $a->toArray();

        $this->assertSame('B', $array['link']['title']);
        $this->assertSame('C', $array['link']['link']['title']);
    }

    #[Test]
    public function it_keeps_the_full_data_of_linked_entries_without_a_cycle()
    {
        $a = $this->page('a', 'b');
        $this->page('b', 'c', ['extra' => 'b-data']);
        $this->page('c', null, ['extra' => 'c-data']);

        $array = $a->toArray();

        $this->assertSame('b-data', $array['link']['extra']);
        $this->assertSame('c-data', $array['link']['link']['extra']);
    }

    #[Test]
    public function it_converts_to_the_same_array_when_called_again()
    {
        $a = $this->page('a', 'b');
        $this->page('b', 'a');

        $this->assertEquals($a->toArray(), $a->toArray());
    }
}

/**
 * A link fieldtype that stops runaway augmentation. Without the cycle guard,
 * entries that link to each other are augmented forever, which would exhaust
 * the memory of the test run. Throwing after a few augmentations makes the
 * tests fail with a clear message instead.
 */
class CountingLink extends Link
{
    // The tests need at most 6 augmentations, this leaves some room.
    private const MAX_AUGMENTATIONS = 25;

    public static int $augmentations = 0;

    protected static $handle = 'counting_link';

    public function augment($value)
    {
        if (++self::$augmentations > self::MAX_AUGMENTATIONS) {
            throw new LogicException('Links are being augmented recursively.');
        }

        return parent::augment($value);
    }
}
