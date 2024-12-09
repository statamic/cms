<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class AmbersandListTest extends TestCase
{
    #[Test]
    public function it_handles_list_with_item_is_string(): void
    {
        $modified = $this->modify('apples');
        $this->assertEquals('apples', $modified);
    }

    #[Test]
    public function it_handles_list_with_one_item(): void
    {
        $modified = $this->modify([
            'apples',
        ]);
        $this->assertEquals('apples', $modified);
    }

    #[Test]
    public function it_creates_an_list_with_default_glue(): void
    {
        $modified = $this->modify([
            'apples',
            'bananas',
            'jerky',
        ]);
        $this->assertEquals('apples, bananas & jerky', $modified);
    }

    #[Test]
    public function it_creates_an_list_from_collection(): void
    {
        $modified = $this->modify(collect([
            'apples',
            'bananas',
            'jerky',
        ]));
        $this->assertEquals('apples, bananas & jerky', $modified);
    }

    #[Test]
    public function it_creates_an_list_with_custom_glue(): void
    {
        $modified = $this->modify([
            'apples',
            'bananas',
            'jerky',
        ], ['&']);
        $this->assertEquals('apples, bananas & jerky', $modified);

        $modified = $this->modify([
            'apples',
            'bananas',
            'jerky',
        ], ['%']);
        $this->assertEquals('apples, bananas % jerky', $modified);

        $modified = $this->modify([
            'apples',
            'bananas',
            'jerky',
        ], ['and']);
        $this->assertEquals('apples, bananas and jerky', $modified);
    }

    private function modify($value, array $params = [])
    {
        return Modify::value($value)->ampersandList($params)->fetch();
    }
}
