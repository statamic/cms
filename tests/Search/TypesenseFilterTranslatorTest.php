<?php

namespace Tests\Search;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Query\OrderBy;
use Statamic\Search\Typesense\FilterTranslator;
use Statamic\Search\Typesense\Schema;
use Tests\TestCase;

class TypesenseFilterTranslatorTest extends TestCase
{
    #[Test]
    public function it_translates_basic_filters_and_escapes_values()
    {
        $translator = new FilterTranslator($this->schema([
            'title' => ['type' => 'string'],
            'price' => ['type' => 'float'],
            'published' => ['type' => 'bool'],
        ]));

        $this->assertSame(
            "((title:=`Jack's \\`book\\`` && price:>=19.95) && published:=true)",
            $translator->filters([
                $this->basic('title', '=', "Jack's `book`"),
                $this->basic('price', '>=', '19.95'),
                $this->basic('published', '=', 1),
            ]),
        );
    }

    #[Test]
    public function it_translates_lists_ranges_and_array_semantics()
    {
        $translator = new FilterTranslator($this->schema([
            'status' => ['type' => 'string'],
            'price' => ['type' => 'int32'],
            'tags' => ['type' => 'string[]'],
        ]));

        $this->assertSame(
            '(((status:=[`published`,`scheduled`] && (price:>=10 && price:<=20)) && (tags:=`php` && tags:=`cms`)) && tags:!=[`draft`,`private`])',
            $translator->filters([
                ['type' => 'In', 'column' => 'status', 'values' => ['published', 'scheduled'], 'boolean' => 'and'],
                ['type' => 'Between', 'column' => 'price', 'values' => [10, 20], 'boolean' => 'and'],
                ['type' => 'JsonContains', 'column' => 'tags', 'values' => ['php', 'cms'], 'boolean' => 'and'],
                ['type' => 'JsonDoesntOverlap', 'column' => 'tags', 'values' => ['draft', 'private'], 'boolean' => 'and'],
            ]),
        );
    }

    #[Test]
    public function it_translates_nested_boolean_groups()
    {
        $translator = new FilterTranslator($this->schema([
            'status' => ['type' => 'string'],
            'site' => ['type' => 'string'],
        ]));

        $nested = new class
        {
            public function rawWheres(): array
            {
                return [
                    [
                        'type' => 'Basic',
                        'column' => 'site',
                        'operator' => '=',
                        'value' => 'en',
                        'boolean' => 'and',
                    ],
                    [
                        'type' => 'Basic',
                        'column' => 'site',
                        'operator' => '=',
                        'value' => 'fr',
                        'boolean' => 'or',
                    ],
                ];
            }
        };

        $this->assertSame(
            '(status:=`published` && (site:=`en` || site:=`fr`))',
            $translator->filters([
                $this->basic('status', '=', 'published'),
                ['type' => 'Nested', 'query' => $nested, 'boolean' => 'and'],
            ]),
        );

        $this->assertSame(
            '((site:=`en` || site:=`fr`) && status:=`published`)',
            $translator->filters([
                $this->basic('site', '=', 'en'),
                array_merge($this->basic('site', '=', 'fr'), ['boolean' => 'or']),
                $this->basic('status', '=', 'published'),
            ]),
        );

        $this->assertSame(
            '(status:=`published` && site:!=[`fr`])',
            $translator->filters([
                $this->basic('status', '=', 'published'),
                ['type' => 'NotIn', 'column' => 'site', 'values' => ['fr'], 'boolean' => 'or'],
            ]),
        );
    }

    #[Test]
    public function unsupported_filters_and_sorts_return_null_instead_of_being_dropped()
    {
        $translator = new FilterTranslator($this->schema([
            'title' => ['type' => 'string', 'sort' => false],
            'date' => ['type' => 'int64', 'sort' => true],
        ]));

        $this->assertNull($translator->filters([
            $this->basic('title', 'like', '%foo%'),
        ]));
        $this->assertNull($translator->filters([
            $this->basic('title', '>', 'foo'),
        ]));
        $this->assertNull($translator->filters([
            ['type' => 'Between', 'column' => 'title', 'values' => ['a', 'z'], 'boolean' => 'and'],
        ]));
        $this->assertNull($translator->filters([
            ['type' => 'Null', 'column' => 'title', 'boolean' => 'and'],
        ]));
        $this->assertNull($translator->filters([
            [
                'type' => 'Nested',
                'query' => new class
                {
                    public function rawWheres(): array
                    {
                        return [];
                    }
                },
                'boolean' => 'and',
            ],
        ]));
        $this->assertNull($translator->filters([
            $this->basic('unknown', '=', 'foo'),
        ]));
        $this->assertNull($translator->sorts([new OrderBy('title', 'asc')]));
        $this->assertNull($translator->sorts([
            new OrderBy('date', 'asc'),
            new OrderBy('date', 'desc'),
            new OrderBy('date', 'asc'),
            new OrderBy('date', 'desc'),
        ]));
        $this->assertSame('date:desc', $translator->sorts([new OrderBy('date', 'desc')]));
    }

    #[Test]
    public function fractional_integer_operands_fall_back_without_truncation()
    {
        $translator = new FilterTranslator($this->schema(['price' => ['type' => 'int32']]));

        $this->assertNull($translator->filters([$this->basic('price', '<', 1.5)]));
        $this->assertNull($translator->filters([
            ['type' => 'Between', 'column' => 'price', 'values' => [1.5, 3]],
        ]));
        $this->assertSame('price:<2', $translator->filters([$this->basic('price', '<', 2.0)]));
    }

    #[Test]
    public function array_membership_is_only_pushed_down_for_json_clauses()
    {
        $translator = new FilterTranslator($this->schema([
            'tags' => ['type' => 'string[]'],
            'title' => ['type' => 'string'],
        ]));

        foreach (['In', 'NotIn'] as $type) {
            $this->assertNull($translator->filters([
                ['type' => $type, 'column' => 'tags', 'values' => ['red']],
            ]));
        }

        $this->assertNull($translator->filters([$this->basic('tags', '=', 'red')]));
        $this->assertNull($translator->filters([
            ['type' => 'JsonContains', 'column' => 'title', 'values' => ['red']],
        ]));
        $this->assertSame('tags:=[`red`]', $translator->filters([
            ['type' => 'JsonOverlaps', 'column' => 'tags', 'values' => ['red']],
        ]));
    }

    private function basic(string $column, string $operator, mixed $value): array
    {
        return compact('column', 'operator', 'value') + [
            'type' => 'Basic',
            'boolean' => 'and',
        ];
    }

    private function schema(array $fields): Schema
    {
        $schema = Mockery::mock(Schema::class);
        $schema->shouldReceive('field')->andReturnUsing(fn (string $name) => $fields[$name] ?? null);
        $schema->shouldReceive('filterable')->andReturnUsing(fn (string $name) => isset($fields[$name]));
        $schema->shouldReceive('sortable')->andReturnUsing(
            fn (string $name) => ($fields[$name]['sort'] ?? false) === true
        );

        return $schema;
    }
}
