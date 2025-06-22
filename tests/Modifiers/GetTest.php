<?php

namespace Modifiers;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GetTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_the_first_item_from_an_array(): void
    {
        $modified = $this->modify(['You', 'shall', 'not pass!']);
        $this->assertEquals('You', $modified);
    }

    #[Test]
    public function it_returns_the_existing_value_if_no_object_can_be_resolved(): void
    {
        $modified = $this->modify('You shall not pass!');
        $this->assertEquals('You shall not pass!', $modified);
    }

    #[Test]
    public function it_returns_the_requested_variable_from_the_array_representation_of_an_augmentable_object(): void
    {
        $id = '1234';
        $entry = EntryFactory::collection('blog')
            ->id($id)
            ->data(['title' => 'Famous Gandalf quotes'])
            ->create();

        $modified = $this->modify($entry, ['title']);
        $this->assertEquals('Famous Gandalf quotes', $modified);

        $modified = $this->modify($id, ['title']);
        $this->assertEquals('Famous Gandalf quotes', $modified);
    }

    #[Test]
    public function it_returns_the_requested_variable_from_the_array_representation_of_a_plain_object(): void
    {
        $object = new class
        {
            public string $title = 'Famous Gandalf quotes';

            public function toArray(): array
            {
                return ['title' => $this->title];
            }
        };

        $modified = $this->modify($object, ['title']);
        $this->assertEquals('Famous Gandalf quotes', $modified);
    }

    #[Test]
    public function it_returns_the_requested_variable_by_method_from_a_plain_object(): void
    {
        $object = new class
        {
            public function title(): string
            {
                return 'Famous Gandalf quotes';
            }

            public function toArray(): array
            {
                return [];
            }
        };

        $modified = $this->modify($object, ['title']);
        $this->assertEquals('Famous Gandalf quotes', $modified);
    }

    #[Test]
    public function it_returns_the_existing_value_if_the_requested_variable_can_not_be_resolved_in_an_object(): void
    {
        $object = new class
        {
            public function toArray(): array
            {
                return [];
            }
        };

        $modified = $this->modify($object, ['title']);
        $this->assertEquals($object, $modified);
    }

    private function modify($value, array $params = [])
    {
        return Modify::value($value)->get($params)->fetch();
    }
}
