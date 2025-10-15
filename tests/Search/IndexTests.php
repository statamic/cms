<?php

namespace Tests\Search;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\Index;

trait IndexTests
{
    public function tearDown(): void
    {
        // Reset the static state of the Index class
        Index::resolveNameUsing(null);

        parent::tearDown();
    }

    #[Test]
    public function it_can_set_a_name_resolver()
    {
        $index = $this->getIndex('myindex');

        $index::resolveNameUsing(function ($name) {
            $this->assertEquals('myindex', $name);

            return 'prefixed_'.$name;
        });

        $this->assertEquals('prefixed_myindex', $index->name());
    }
}
