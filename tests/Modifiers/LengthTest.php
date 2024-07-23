<?php

namespace Tests\Modifiers;

use Illuminate\Contracts\Support\Arrayable;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Query\Builder;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class LengthTest extends TestCase
{
    #[Test]
    public function it_returns_the_numbers_of_items_in_array(): void
    {
        $arr = [
            'Taylor Swift',
            'Left Shark',
            'Leroy Jenkins',
        ];
        $modified = $this->modify($arr);
        $this->assertSame(3, $modified);
    }

    #[Test]
    public function it_returns_the_numbers_of_items_in_collection(): void
    {
        $arr = collect([
            'Taylor Swift',
            'Left Shark',
            'Leroy Jenkins',
        ]);
        $modified = $this->modify($arr);
        $this->assertSame(3, $modified);
    }

    #[Test]
    public function it_returns_the_number_of_items_in_a_query()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('count')->once()->andReturn(3);
        $modified = $this->modify($builder);
        $this->assertSame(3, $modified);
    }

    #[Test]
    public function it_returns_the_number_of_items_in_an_arrayable()
    {
        $arrayable = Mockery::mock(Arrayable::class)->shouldReceive('toArray')->andReturn(['one', 'two'])->getMock();

        $modified = $this->modify($arrayable);
        $this->assertSame(2, $modified);
    }

    #[Test]
    public function it_returns_the_numbers_of_chars_in_string(): void
    {
        $string = 'LEEEEROOOYYYY JEEENKINNNSS!';
        $modified = $this->modify($string);
        $this->assertSame(27, $modified);
    }

    #[Test]
    public function it_counts_a_collection_instead_of_toarraying_it(): void
    {
        $itemOne = Mockery::mock(Arrayable::class)->shouldNotReceive('toArray')->getMock();
        $itemTwo = Mockery::mock(Arrayable::class)->shouldNotReceive('toArray')->getMock();

        $arr = collect([$itemOne, $itemTwo]);
        $modified = $this->modify($arr);
        $this->assertSame(2, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->length()->fetch();
    }
}
