<?php

namespace Modifiers;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Entry;
use Statamic\Modifiers\Modify;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ResolveTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_resolves_a_query_builder(): void
    {
        $collection = 'blog';
        $entry = EntryFactory::collection($collection)
            ->data(['title' => 'Famous Gandalf quotes'])
            ->create();

        $modified = $this->modify(Entry::query()->where('collection', $collection));
        $this->assertIsArray($modified);
        $this->assertEquals($entry, $modified[0]);

        $modified = $this->modify(Entry::query()->where('collection', $collection), [0]);
        $this->assertEquals($entry, $modified);
    }

    #[Test]
    public function it_resolves_a_collection(): void
    {
        $modified = $this->modify(collect(['You shall not pass!', 'Fool of a Took']));
        $this->assertIsArray($modified);
        $this->assertEquals(['You shall not pass!', 'Fool of a Took'], $modified);

        $modified = $this->modify(collect(['You shall not pass!', 'Fool of a Took']), [1]);
        $this->assertEquals('Fool of a Took', $modified);

        $modified = $this->modify(collect(['one' => 'You shall not pass!', 'two' => 'Fool of a Took']), ['two']);
        $this->assertEquals('Fool of a Took', $modified);
    }

    #[Test]
    public function it_resolves_an_array(): void
    {
        $modified = $this->modify(['You shall not pass!', 'Fool of a Took']);
        $this->assertIsArray($modified);
        $this->assertEquals(['You shall not pass!', 'Fool of a Took'], $modified);

        $modified = $this->modify(['You shall not pass!', 'Fool of a Took'], [1]);
        $this->assertEquals('Fool of a Took', $modified);

        $modified = $this->modify(['one' => 'You shall not pass!', 'two' => 'Fool of a Took'], ['two']);
        $this->assertEquals('Fool of a Took', $modified);
    }

    private function modify($value, array $params = [])
    {
        return Modify::value($value)->resolve($params)->fetch();
    }
}
