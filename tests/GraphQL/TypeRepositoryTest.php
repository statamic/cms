<?php

namespace Tests\GraphQL;

use Statamic\GraphQL\TypeRepository;
use Statamic\GraphQL\Types\ObjectType;
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
    public function it_gets_a_type_by_class_and_stores_it_using_the_name()
    {
        $type = $this->types->get(TestObjectType::class);

        $this->assertInstanceOf(TestObjectType::class, $type);
        $this->assertSame($type, $this->types->get(TestObjectType::class));
        $this->assertSame($type, $this->types->get('TheTestObject'));
    }

    /** @test */
    public function it_gets_the_query_type_using_dedicated_method()
    {
        $query = $this->types->query();

        $this->assertInstanceOf(Query::class, $query);
        $this->assertSame($query, $this->types->query());
    }

    /** @test */
    public function it_registers_a_type_by_name()
    {
        $this->assertNull($this->types->get('test'));

        $type = new TestObjectType([]);

        $this->types->register('test', $type);

        $this->assertSame($type, $this->types->get('test'));
        $this->assertEquals(['test' => $type], $this->types->registered());
    }
}

class TestObjectType extends ObjectType
{
    public static function name(): string
    {
        return 'TheTestObject';
    }

    public function config(array $args): array
    {
        return [];
    }
}
