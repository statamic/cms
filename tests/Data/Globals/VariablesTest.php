<?php

namespace Tests\Data\Globals;

use Statamic\Facades\GlobalSet;
use Statamic\Globals\Variables;
use Tests\TestCase;

class VariablesTest extends TestCase
{
    /** @test */
    public function it_gets_file_contents_for_saving()
    {
        $entry = (new Variables)->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
            'null' => null, // this...
            'empty' => [],  // and this should get stripped out because it's the root. there's no origin to fall back to.
        ]);

        $expected = <<<'EOT'
array:
  - 'first one'
  - 'second one'
string: 'The string'

EOT;
        $this->assertEquals($expected, $entry->fileContents());
    }

    /** @test */
    public function it_gets_file_contents_for_saving_a_localized_set()
    {
        $global = GlobalSet::make('test');

        $a = $global->makeLocalization('a')->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
            'null' => null, // this...
            'empty' => [],  // and this should get stripped out because there's no origin to fall back to.
        ]);

        $b = $global->makeLocalization('b')->origin($a)->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
            'null' => null, // this...
            'empty' => [],  // and this should not get stripped out, otherwise it would fall back to the origin.
        ]);

        $c = $global->makeLocalization('c')->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
            'null' => null, // this...
            'empty' => [],  // and this should get stripped out because there's no origin to fall back to.
        ]);

        $expected = <<<'EOT'
array:
  - 'first one'
  - 'second one'
string: 'The string'

EOT;
        $this->assertEquals($expected, $a->fileContents());

        $expected = <<<'EOT'
array:
  - 'first one'
  - 'second one'
string: 'The string'
'null': null
empty: {  }
origin: a

EOT;
        $this->assertEquals($expected, $b->fileContents());

        $expected = <<<'EOT'
array:
  - 'first one'
  - 'second one'
string: 'The string'

EOT;
        $this->assertEquals($expected, $c->fileContents());
    }

    /** @test */
    public function if_the_value_is_explicitly_set_to_null_then_it_should_not_fall_back()
    {
        $global = GlobalSet::make('test');

        $a = $global->makeLocalization('a')->data([
            'one' => 'alfa',
            'two' => 'bravo',
            'three' => 'charlie',
            'four' => 'delta',
        ]);

        // originates from a
        $b = $global->makeLocalization('b')->origin($a)->data([
            'one' => 'echo',
            'two' => null,
        ]);

        // originates from b, which originates from a
        $c = $global->makeLocalization('c')->origin($b)->data([
            'three' => 'foxtrot',
        ]);

        // does not originate from anything
        $d = $global->makeLocalization('d')->data([
            'one' => 'golf',
            'two' => 'hotel',
            'three' => 'india',
        ]);

        // originates from d. just to test that it doesn't unintentionally fall back to the default/first.
        $e = $global->makeLocalization('e')->origin($d)->data([
            'one' => 'juliett',
            'two' => null,
        ]);

        $this->assertEquals([
            'one' => 'alfa',
            'two' => 'bravo',
            'three' => 'charlie',
            'four' => 'delta',
        ], $a->values()->all());
        $this->assertEquals('alfa', $a->value('one'));
        $this->assertEquals('bravo', $a->value('two'));
        $this->assertEquals('charlie', $a->value('three'));
        $this->assertEquals('delta', $a->value('four'));

        $this->assertEquals([
            'one' => 'echo',
            'two' => null,
            'three' => 'charlie',
            'four' => 'delta',
        ], $b->values()->all());
        $this->assertEquals('echo', $b->value('one'));
        $this->assertEquals(null, $b->value('two'));
        $this->assertEquals('charlie', $b->value('three'));
        $this->assertEquals('delta', $b->value('four'));

        $this->assertEquals([
            'one' => 'echo',
            'two' => null,
            'three' => 'foxtrot',
            'four' => 'delta',
        ], $c->values()->all());
        $this->assertEquals('echo', $c->value('one'));
        $this->assertEquals(null, $c->value('two'));
        $this->assertEquals('foxtrot', $c->value('three'));
        $this->assertEquals('delta', $c->value('four'));

        $this->assertEquals([
            'one' => 'golf',
            'two' => 'hotel',
            'three' => 'india',
        ], $d->values()->all());
        $this->assertEquals('golf', $d->value('one'));
        $this->assertEquals('hotel', $d->value('two'));
        $this->assertEquals('india', $d->value('three'));
        $this->assertEquals(null, $d->value('four'));

        $this->assertEquals([
            'one' => 'juliett',
            'two' => null,
            'three' => 'india',
        ], $e->values()->all());
        $this->assertEquals('juliett', $e->value('one'));
        $this->assertEquals(null, $e->value('two'));
        $this->assertEquals('india', $e->value('three'));
        $this->assertEquals(null, $e->value('four'));
    }
}
