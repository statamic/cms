<?php

namespace Tests\Dictionaries;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Dictionary;
use Statamic\Dictionaries\DictionaryRepository;
use Statamic\Dictionaries\Item;
use Tests\TestCase;

class DictionaryRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->repo = new DictionaryRepository;

        FakeDictionary::register();
    }

    #[Test]
    public function can_get_all_dictionaries()
    {
        $all = $this->repo->all();

        $this->assertCount(7, $all); // The built-in dictionaries + our fake one
        $this->assertEveryItem($all, fn ($item) => $item instanceof Dictionary);
    }

    #[Test]
    public function can_get_a_dictionary()
    {
        $find = $this->repo->find('fake_dictionary');

        $this->assertInstanceOf(Dictionary::class, $find);
        $this->assertSame('fake_dictionary', $find->handle());
    }

    #[Test]
    public function ensure_context_is_passed_to_dictionary()
    {
        $dictionary = $this->repo->find('fake_dictionary', [
            'sort_in_alphabetical_order' => true,
        ]);

        // When the sort_in_alphabetical_order context is passed,
        // the options should be returned in alphabetical order.
        $this->assertEquals([
            'bar' => 'Bar',
            'baz' => 'Baz',
            'foo' => 'Foo',
            'qux' => 'Qux',
        ], $dictionary->options());
    }
}

class FakeDictionary extends Dictionary
{
    public function options(?string $search = null): array
    {
        return $this->data()
            ->when($search ?? false, function ($collection) use ($search) {
                return $collection->filter(fn ($item) => str_contains($item['id'], $search));
            })
            ->mapWithKeys(fn ($item) => [$item['id'] => $item['name']])
            ->when($this->config['sort_in_alphabetical_order'] ?? false, function ($collection) {
                return $collection->sortBy('id');
            })
            ->all();
    }

    public function get(string $key): ?Item
    {
        return $this->data()->firstWhere('id', $key);
    }

    protected function data()
    {
        return collect([
            ['name' => 'Foo', 'id' => 'foo'],
            ['name' => 'Bar', 'id' => 'bar'],
            ['name' => 'Baz', 'id' => 'baz'],
            ['name' => 'Qux', 'id' => 'qux'],
        ]);
    }

    protected function fieldItems()
    {
        return [
            'sort_in_alphabetical_order' => [
                'display' => 'Sort in alphabetical order?',
                'type' => 'toggle',
            ],
        ];
    }
}
