<?php

namespace Tests\GraphQL;

use Statamic\GraphQL\TypeRepository;
use Statamic\GraphQL\Types\Query;
use Tests\TestCase;

/** @group graphql */
class TypeRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->types = new TypeRepository;
    }

    /** @test */
    public function it_gets_a_type_by_class()
    {
        $query = $this->types->get(Query::class);

        $this->assertInstanceOf(Query::class, $query);
        $this->assertSame($query, $this->types->get(Query::class));
    }

    /** @test */
    public function it_gets_the_query_type_using_dedicated_method()
    {
        $query = $this->types->query();

        $this->assertInstanceOf(Query::class, $query);
        $this->assertSame($query, $this->types->query());
    }
}
