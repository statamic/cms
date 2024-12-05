<?php

namespace Tests\Tags\Concerns;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Fields\LabeledValue;
use Statamic\Query\Builder;
use Statamic\Tags\Collection\Entries;
use Statamic\Tags\Concerns\QueriesConditions;
use Statamic\Tags\Context;
use Statamic\Tags\Parameters;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class QueriesConditionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $collection;

    public function setUp(): void
    {
        parent::setUp();

        $this->collection = Facades\Collection::make('test')->save();
    }

    protected function makeEntry($slug)
    {
        return Facades\Entry::make()
            ->locale('en')
            ->slug($slug)
            ->collection($this->collection);
    }

    protected function getEntries($params = [])
    {
        $params['from'] = 'test';

        $params = Parameters::make($params, new Context);

        return (new Entries($params))->get();
    }

    #[Test]
    public function it_filters_by_is_condition()
    {
        $this->makeEntry('dog')->set('title', 'Dog')->save();
        $this->makeEntry('cat')->set('title', 'Cat')->save();
        $this->makeEntry('tiger')->set('title', 'Tiger')->save();
        $this->makeEntry('rat')->set('featured', true)->save();
        $this->makeEntry('bat')->set('featured', false)->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(1, $this->getEntries(['title:is' => 'dog']));
        $this->assertCount(1, $this->getEntries(['title:equals' => 'dog']));
        $this->assertCount(1, $this->getEntries(['featured:is' => true]));
        $this->assertCount(4, $this->getEntries(['featured:is' => false]));
    }

    #[Test]
    public function it_does_not_filter_by_is_condition_when_value_is_empty()
    {
        $this->makeEntry('a')->set('author', 'john-doe')->save();
        $this->makeEntry('b')->set('author', 'david-hasselhoff')->save();
        $this->makeEntry('c')->set('author', 'josiah-bartlet')->save();

        $this->assertCount(3, $this->getEntries(['author:is' => '']));
    }

    #[Test]
    public function it_filters_by_not_condition()
    {
        $this->makeEntry('dog')->set('title', 'Dog')->save();
        $this->makeEntry('cat')->set('title', 'Cat')->save();
        $this->makeEntry('tiger')->set('title', 'Tiger')->save();
        $this->makeEntry('rat')->set('featured', true)->save();
        $this->makeEntry('bat')->set('featured', false)->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['title:not' => 'dog']));
        $this->assertCount(4, $this->getEntries(['title:isnt' => 'dog']));
        $this->assertCount(4, $this->getEntries(['title:aint' => 'dog']));
        $this->assertCount(4, $this->getEntries(['title:¯\\_(ツ)_/¯' => 'dog']));
        $this->assertCount(4, $this->getEntries(['featured:not' => true]));
        $this->assertCount(4, $this->getEntries(['featured:not' => false]));
    }

    #[Test]
    public function it_does_not_filter_by_not_condition_when_value_is_empty()
    {
        $this->makeEntry('a')->set('author', 'john-doe')->save();
        $this->makeEntry('b')->set('author', 'david-hasselhoff')->save();
        $this->makeEntry('c')->set('author', 'josiah-bartlet')->save();

        $this->assertCount(3, $this->getEntries(['author:not' => '']));
    }

    #[Test]
    public function it_filters_by_contains_condition()
    {
        $this->makeEntry('dog')->set('title', 'Dog Stories')->save();
        $this->makeEntry('cat')->set('title', 'Cat Fables')->save();
        $this->makeEntry('tiger')->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(1, $this->getEntries(['title:contains' => 'sto']));
    }

    #[Test]
    public function it_does_not_filter_by_contains_condition_when_value_is_empty()
    {
        $this->makeEntry('dog')->set('title', 'Dog Stories')->save();
        $this->makeEntry('cat')->set('title', 'Cat Fables')->save();
        $this->makeEntry('tiger')->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries(['title:contains' => '']));
    }

    #[Test]
    public function it_filters_by_doesnt_contain_condition()
    {
        $this->makeEntry('dog')->set('title', 'Dog Stories')->save();
        $this->makeEntry('cat')->set('title', 'Cat Fables')->save();
        $this->makeEntry('tiger')->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['title:doesnt_contain' => 'sto']));
    }

    #[Test]
    public function it_does_not_filter_by_doesnt_contains_condition_when_value_is_empty()
    {
        $this->makeEntry('dog')->set('title', 'Dog Stories')->save();
        $this->makeEntry('cat')->set('title', 'Cat Fables')->save();
        $this->makeEntry('tiger')->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries(['title:doesnt_contain' => '']));
    }

    #[Test]
    public function it_filters_by_in_condition()
    {
        $this->makeEntry('dog')->set('type', 'canine')->save();
        $this->makeEntry('wolf')->set('type', 'canine')->save();
        $this->makeEntry('tiger')->set('type', 'feline')->save();
        $this->makeEntry('cat')->set('type', 'feline')->save();
        $this->makeEntry('lion')->set('type', 'feline')->save();
        $this->makeEntry('horse')->set('type', 'equine')->save();
        $this->makeEntry('bigfoot')->save();

        $this->assertCount(7, $this->getEntries());
        $this->assertEquals(['dog', 'wolf'], $this->getEntries(['type:in' => ['canine']])->map->slug()->all());
        $this->assertEquals(['tiger', 'cat', 'lion'], $this->getEntries(['type:in' => ['feline']])->map->slug()->all());
        $this->assertEquals(['dog', 'wolf', 'tiger', 'cat', 'lion'], $this->getEntries(['type:in' => ['canine', 'feline']])->map->slug()->all());
        $this->assertEquals(['horse'], $this->getEntries(['type:in' => ['equine']])->map->slug()->all());

        // Handles pipe array syntax
        $this->assertEquals(
            ['dog', 'wolf', 'tiger', 'cat', 'lion'],
            $this->getEntries(['type:in' => 'canine|feline'])->map->slug()->all()
        );
    }

    #[Test]
    public function it_filters_by_not_in_condition()
    {
        $this->makeEntry('dog')->set('type', 'canine')->save();
        $this->makeEntry('wolf')->set('type', 'canine')->save();
        $this->makeEntry('tiger')->set('type', 'feline')->save();
        $this->makeEntry('cat')->set('type', 'feline')->save();
        $this->makeEntry('lion')->set('type', 'feline')->save();
        $this->makeEntry('horse')->set('type', 'equine')->save();
        $this->makeEntry('bigfoot')->save();

        $this->assertCount(7, $this->getEntries());
        $this->assertEquals(
            ['tiger', 'cat', 'lion', 'horse', 'bigfoot'],
            $this->getEntries(['type:not_in' => ['canine']])->map->slug()->all()
        );
        $this->assertEquals(
            ['dog', 'wolf', 'horse', 'bigfoot'],
            $this->getEntries(['type:not_in' => ['feline']])->map->slug()->all()
        );
        $this->assertEquals(
            ['horse', 'bigfoot'],
            $this->getEntries(['type:not_in' => ['canine', 'feline']])->map->slug()->all()
        );
        $this->assertEquals(
            ['dog', 'wolf', 'tiger', 'cat', 'lion', 'bigfoot'],
            $this->getEntries(['type:not_in' => ['equine']])->map->slug()->all()
        );

        // Handles pipe array syntax
        $this->assertEquals(
            ['horse', 'bigfoot'],
            $this->getEntries(['type:not_in' => 'canine|feline'])->map->slug()->all()
        );
    }

    #[Test]
    public function it_filters_by_starts_with_condition()
    {
        $this->makeEntry('dog')->set('title', 'Dog Stories')->save();
        $this->makeEntry('cat')->set('title', 'Cat Fables')->save();
        $this->makeEntry('tiger')->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['title:starts_with' => 'sto']));
        $this->assertCount(0, $this->getEntries(['title:begins_with' => 'sto']));
        $this->assertCount(1, $this->getEntries(['title:starts_with' => 'dog']));
        $this->assertCount(1, $this->getEntries(['title:begins_with' => 'dog']));
    }

    #[Test]
    public function it_filters_by_doesnt_start_with_condition()
    {
        $this->makeEntry('dog')->set('title', 'Dog Stories')->save();
        $this->makeEntry('cat')->set('title', 'Cat Fables')->save();
        $this->makeEntry('tiger')->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['title:doesnt_start_with' => 'sto']));
        $this->assertCount(3, $this->getEntries(['title:doesnt_begin_with' => 'sto']));
        $this->assertCount(2, $this->getEntries(['title:doesnt_start_with' => 'dog']));
        $this->assertCount(2, $this->getEntries(['title:doesnt_begin_with' => 'dog']));
    }

    #[Test]
    public function it_filters_by_ends_with_condition()
    {
        $this->makeEntry('dog')->set('title', 'Dog Stories')->save();
        $this->makeEntry('cat')->set('title', 'Cat Fables')->save();
        $this->makeEntry('tiger')->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['title:ends_with' => 'sto']));
        $this->assertCount(1, $this->getEntries(['title:ends_with' => 'stories']));
    }

    #[Test]
    public function it_filters_by_doesnt_end_with_condition()
    {
        $this->makeEntry('dog')->set('title', 'Dog Stories')->save();
        $this->makeEntry('cat')->set('title', 'Cat Fables')->save();
        $this->makeEntry('tiger')->set('title', 'Tiger Tales')->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['title:doesnt_end_with' => 'sto']));
        $this->assertCount(2, $this->getEntries(['title:doesnt_end_with' => 'stories']));
    }

    #[Test]
    public function it_filters_by_greater_than_condition()
    {
        $this->makeEntry('a')->set('age', 11)->save();
        $this->makeEntry('b')->set('age', '11')->save();
        $this->makeEntry('c')->set('age', 21)->save();
        $this->makeEntry('d')->set('age', '21')->save();
        $this->makeEntry('e')->set('age', 24)->save();
        $this->makeEntry('f')->set('age', '24')->save();

        $this->assertCount(6, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['age:greater_than' => 18]));
        $this->assertCount(4, $this->getEntries(['age:gt' => 18]));
        $this->assertCount(4, $this->getEntries(['age:greater_than' => '18']));
        $this->assertCount(4, $this->getEntries(['age:gt' => '18']));
    }

    #[Test]
    public function it_filters_by_less_than_condition()
    {
        $this->makeEntry('a')->set('age', 11)->save();
        $this->makeEntry('b')->set('age', '11')->save();
        $this->makeEntry('c')->set('age', 21)->save();
        $this->makeEntry('d')->set('age', '21')->save();
        $this->makeEntry('e')->set('age', 24)->save();
        $this->makeEntry('f')->set('age', '24')->save();

        $this->assertCount(6, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['age:less_than' => 18]));
        $this->assertCount(2, $this->getEntries(['age:lt' => 18]));
        $this->assertCount(2, $this->getEntries(['age:less_than' => '18']));
        $this->assertCount(2, $this->getEntries(['age:lt' => '18']));
    }

    #[Test]
    public function it_filters_by_greater_than_or_equal_to_condition()
    {
        $this->makeEntry('a')->set('age', 11)->save();
        $this->makeEntry('b')->set('age', '11')->save();
        $this->makeEntry('c')->set('age', 21)->save();
        $this->makeEntry('d')->set('age', '21')->save();
        $this->makeEntry('e')->set('age', 24)->save();
        $this->makeEntry('f')->set('age', '24')->save();

        $this->assertCount(6, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['age:greater_than_or_equal_to' => 21]));
        $this->assertCount(4, $this->getEntries(['age:gte' => 21]));
        $this->assertCount(4, $this->getEntries(['age:greater_than_or_equal_to' => '21']));
        $this->assertCount(4, $this->getEntries(['age:gte' => '21']));
    }

    #[Test]
    public function it_filters_by_less_than_or_equal_to_condition()
    {
        $this->makeEntry('a')->set('age', 11)->save();
        $this->makeEntry('b')->set('age', '11')->save();
        $this->makeEntry('c')->set('age', 21)->save();
        $this->makeEntry('d')->set('age', '21')->save();
        $this->makeEntry('e')->set('age', 24)->save();
        $this->makeEntry('f')->set('age', '24')->save();

        $this->assertCount(6, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['age:less_than_or_equal_to' => 21]));
        $this->assertCount(4, $this->getEntries(['age:lte' => 21]));
        $this->assertCount(4, $this->getEntries(['age:less_than_or_equal_to' => '21']));
        $this->assertCount(4, $this->getEntries(['age:lte' => '21']));
    }

    #[Test]
    public function it_filters_by_regex_condition()
    {
        $this->makeEntry('a')->set('title', 'Dog Stories')->save();
        $this->makeEntry('b')->set('title', 'Cat Fables')->save();
        $this->makeEntry('c')->set('title', 'Tiger Tales')->save();
        $this->makeEntry('d')->set('title', 'Why I Love My Cat')->save();
        $this->makeEntry('e')->set('title', 'Paw Poetry')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['title:matches' => 'cat']));
        $this->assertCount(2, $this->getEntries(['title:match' => 'cat']));
        $this->assertCount(2, $this->getEntries(['title:regex' => 'cat']));
        $this->assertCount(1, $this->getEntries(['title:matches' => '^cat']));
        $this->assertCount(1, $this->getEntries(['title:match' => '^cat']));
        $this->assertCount(1, $this->getEntries(['title:regex' => '^cat']));
        $this->assertCount(1, $this->getEntries(['title:matches' => 'c.t$']));
        $this->assertCount(1, $this->getEntries(['title:match' => 'c.t$']));
        $this->assertCount(1, $this->getEntries(['title:regex' => 'c.t$']));
        $this->assertCount(1, $this->getEntries(['title:matches' => '/^cat/']));  // v2 patterns required delimiters
        $this->assertCount(1, $this->getEntries(['title:matches' => '/^cat/i'])); // v2 patterns required delimiters
    }

    #[Test]
    public function it_filters_by_not_regex_condition()
    {
        $this->makeEntry('a')->set('title', 'Dog Stories')->save();
        $this->makeEntry('b')->set('title', 'Cat Fables')->save();
        $this->makeEntry('c')->set('title', 'Tiger Tales')->save();
        $this->makeEntry('d')->set('title', 'Why I Love My Cat')->save();
        $this->makeEntry('e')->set('title', 'Paw Poetry')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['title:doesnt_match' => 'cat']));
        $this->assertCount(4, $this->getEntries(['title:doesnt_match' => '^cat']));
        $this->assertCount(4, $this->getEntries(['title:doesnt_match' => 'c.t$']));
        $this->assertCount(4, $this->getEntries(['title:doesnt_match' => '/^cat/']));  // v2 patterns required delimiters
        $this->assertCount(4, $this->getEntries(['title:doesnt_match' => '/^cat/i'])); // v2 patterns required delimiters
    }

    #[Test]
    public function it_filters_by_is_after_or_before_date_conditions()
    {
        $this->collection->dated(true)->save();
        $blueprint = Blueprint::makeFromFields(['date' => ['type' => 'date', 'time_enabled' => true, 'time_seconds_enabled' => true]])->setHandle('test');
        Blueprint::shouldReceive('in')->with('collections/test')->once()->andReturn(collect([$blueprint]));

        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

        $this->makeEntry('a')->date('2019-03-09')->save(); // definitely in past
        $this->makeEntry('b')->date('2019-03-10')->save(); // today
        $this->makeEntry('c')->date('2019-03-10-1259')->save(); // today, but before "now"
        $this->makeEntry('e')->date('2019-03-10-1300')->save(); // today, and also "now"
        $this->makeEntry('f')->date('2019-03-10-1301')->save(); // today, but after "now"
        $this->makeEntry('g')->date('2019-03-11')->save(); // definitely in future

        $this->assertCount(6, $this->getEntries(['show_future' => true]));

        $this->assertCount(3, $this->getEntries(['show_future' => true, 'date:is_before' => true]));
        $this->assertCount(3, $this->getEntries(['show_future' => true, 'date:is_past' => true]));
        $this->assertCount(1, $this->getEntries(['show_future' => true, 'date:is_before' => 'today']));
        $this->assertCount(1, $this->getEntries(['show_future' => true, 'date:is_past' => 'today']));
        $this->assertCount(2, $this->getEntries(['show_future' => true, 'date:is_before' => false]));
        $this->assertCount(2, $this->getEntries(['show_future' => true, 'date:is_past' => false]));

        $this->assertCount(2, $this->getEntries(['show_future' => true, 'date:is_after' => true]));
        $this->assertCount(2, $this->getEntries(['show_future' => true, 'date:is_future' => true]));
        $this->assertCount(4, $this->getEntries(['show_future' => true, 'date:is_after' => 'today']));
        $this->assertCount(4, $this->getEntries(['show_future' => true, 'date:is_future' => 'today']));
        $this->assertCount(3, $this->getEntries(['show_future' => true, 'date:is_after' => false]));
        $this->assertCount(3, $this->getEntries(['show_future' => true, 'date:is_future' => false]));

        $time = Carbon::parse('2019-03-10 13:02');
        $this->assertCount(5, $this->getEntries(['show_future' => true, 'date:is_before' => $time]));
        $this->assertCount(1, $this->getEntries(['show_future' => true, 'date:is_after' => $time]));
    }

    #[Test]
    public function it_filters_by_is_after_or_before_date_range_conditions()
    {
        $this->collection->save();
        $blueprint = Blueprint::makeFromFields(['date' => ['type' => 'date', 'mode' => 'range', 'time_enabled' => true, 'time_seconds_enabled' => true]])->setHandle('test');
        Blueprint::shouldReceive('in')->with('collections/test')->once()->andReturn(collect([$blueprint]));

        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

        $this->makeEntry('a')->data(['date_field' => ['start' => '2019-03-09', 'end' => '2019-03-10']])->save(); // definitely in past
        $this->makeEntry('b')->data(['date_field' => ['start' => '2019-03-10', 'end' => '2019-03-11']])->save(); // today
        $this->makeEntry('c')->data(['date_field' => ['start' => '2019-03-11', 'end' => '2019-03-18']])->save(); // today, but before "now"
        $this->makeEntry('e')->data(['date_field' => ['start' => '2019-03-11', 'end' => '2019-03-16']])->save(); // today, and also "now"
        $this->makeEntry('f')->data(['date_field' => ['start' => '2019-03-12', 'end' => '2019-03-14']])->save(); // today, but after "now"
        $this->makeEntry('g')->data(['date_field' => ['start' => '2019-03-11', 'end' => '2019-03-12']])->save(); // definitely in future

        $this->assertCount(6, $this->getEntries([]));

        $this->assertCount(2, $this->getEntries(['date_field.start:is_before' => true]));
        $this->assertCount(2, $this->getEntries(['date_field.start:is_past' => true]));
        $this->assertCount(2, $this->getEntries(['date_field.start:is_before' => 'today']));
        $this->assertCount(2, $this->getEntries(['date_field.start:is_past' => 'today']));
        $this->assertCount(4, $this->getEntries(['date_field.start:is_before' => false]));
        $this->assertCount(4, $this->getEntries(['date_field.start:is_past' => false]));

        $this->assertCount(4, $this->getEntries(['date_field.start:is_after' => true]));
        $this->assertCount(4, $this->getEntries(['date_field.start:is_future' => true]));
        $this->assertCount(4, $this->getEntries(['date_field.start:is_after' => 'today']));
        $this->assertCount(4, $this->getEntries(['date_field.start:is_future' => 'today']));
        $this->assertCount(2, $this->getEntries(['date_field.start:is_after' => false]));
        $this->assertCount(2, $this->getEntries(['date_field.start:is_future' => false]));
    }

    #[Test]
    public function it_filters_by_is_alpha_condition()
    {
        $this->makeEntry('a')->set('title', 'Post')->save();
        $this->makeEntry('b')->set('title', 'Post Two')->save();
        $this->makeEntry('c')->set('title', 'It\'s a post')->save();
        $this->makeEntry('d')->set('title', 'Post1')->save();
        $this->makeEntry('e')->set('title', 'Post 2')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(1, $this->getEntries(['title:is_alpha' => true]));
        $this->assertCount(4, $this->getEntries(['title:is_alpha' => false]));
    }

    #[Test]
    public function it_filters_by_is_alpha_numeric_condition()
    {
        $this->makeEntry('a')->set('title', 'Post')->save();
        $this->makeEntry('b')->set('title', 'Post Two')->save();
        $this->makeEntry('c')->set('title', 'It\'s a post')->save();
        $this->makeEntry('d')->set('title', 'Post1')->save();
        $this->makeEntry('e')->set('title', 'Post 2')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['title:is_alpha_numeric' => true]));
        $this->assertCount(3, $this->getEntries(['title:is_alpha_numeric' => false]));
    }

    #[Test]
    public function it_filters_by_is_numeric_condition()
    {
        $this->makeEntry('a')->set('title', 'Post')->save();
        $this->makeEntry('b')->set('title', 'Post Two')->save();
        $this->makeEntry('c')->set('title', 'It\'s a post')->save();
        $this->makeEntry('d')->set('title', '1.2.3')->save();
        $this->makeEntry('e')->set('title', '1 2')->save();
        $this->makeEntry('f')->set('title', '1')->save(); // integer
        $this->makeEntry('g')->set('title', '1.2')->save(); // float
        $this->makeEntry('h')->set('title', '.2')->save(); // float

        $this->assertCount(8, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['title:is_numeric' => true]));
        $this->assertCount(5, $this->getEntries(['title:is_numeric' => false]));
    }

    #[Test]
    public function it_filters_by_is_url_condition()
    {
        $this->makeEntry('a')->set('website', 'https://domain.tld')->save();
        $this->makeEntry('b')->set('website', 'http://domain.tld')->save();
        $this->makeEntry('c')->set('website', 'https://www.domain.tld/uri/segment.extension?param=one&two=true')->save();
        $this->makeEntry('d')->set('website', 'http://www.domain.tld/uri/segment.extension?param=one&two=true')->save();
        $this->makeEntry('e')->set('website', 'http://')->save();
        $this->makeEntry('f')->set('website', ' http://')->save();
        $this->makeEntry('g')->set('website', 'http://domain with space.tld')->save();
        $this->makeEntry('h')->set('website', 'domain-only.tld')->save();
        $this->makeEntry('i')->set('website', 'definitely not a url')->save();

        $this->assertCount(9, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['website:is_url' => true]));
        $this->assertCount(5, $this->getEntries(['website:is_url' => false]));

        $this->getEntries(['website:is_url' => true])->map->get('website')->each(function ($url) {
            $this->assertStringContainsString('domain.tld', $url);
        });
    }

    #[Test]
    public function it_filters_by_is_embeddable_condition()
    {
        $this->makeEntry('a')->set('video', 'https://youtube.com/id')->save(); // valid
        $this->makeEntry('b')->set('video', 'http://youtube.com/some/id')->save(); // valid
        $this->makeEntry('c')->set('video', 'youtube.com/id')->save(); // not url
        $this->makeEntry('d')->set('video', 'http://youtube.com/')->save(); // no id

        $this->makeEntry('e')->set('video', 'https://vimeo.com/id')->save();
        $this->makeEntry('f')->set('video', 'http://vimeo.com/some/id')->save();
        $this->makeEntry('g')->set('video', 'vimeo.com/id')->save();
        $this->makeEntry('h')->set('video', 'http://vimeo.com/')->save();

        $this->makeEntry('i')->set('video', 'https://youtu.be/id')->save();
        $this->makeEntry('j')->set('video', 'http://youtu.be/some/id')->save();
        $this->makeEntry('k')->set('video', 'youtu.be/id')->save();
        $this->makeEntry('l')->set('video', 'http://youtu.be/')->save();

        $this->assertCount(12, $this->getEntries());
        $this->assertCount(6, $this->getEntries(['video:is_embeddable' => true]));
        $this->assertCount(6, $this->getEntries(['video:is_embeddable' => false]));

        $this->getEntries(['video:is_embeddable' => true])->map->get('video')->each(function ($url) {
            $this->assertStringContainsString('http', $url);
            $this->assertStringContainsString('/id', $url);
        });
    }

    #[Test]
    public function it_filters_by_is_email_condition()
    {
        $this->makeEntry('a')->set('email', 'han@solo.com')->save();
        $this->makeEntry('b')->set('email', 'darth.jar-jar@sith.gov.naboo.com')->save();
        $this->makeEntry('c')->set('email', 'not@email')->save();
        $this->makeEntry('d')->set('email', 'not.email')->save();
        $this->makeEntry('e')->set('email', 'definitely not email')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['email:is_email' => true]));
        $this->assertCount(3, $this->getEntries(['email:is_email' => false]));

        $this->getEntries(['email:is_email' => true])->map->get('email')->each(function ($email) {
            $this->assertStringContainsString('.com', $email);
        });
    }

    #[Test]
    public function it_filters_by_is_empty_condition()
    {
        $this->makeEntry('a')->set('sub_title', 'Has sub-title')->save();
        $this->makeEntry('b')->set('sub_title', '')->save();
        $this->makeEntry('c')->set('sub_title', null)->save();
        $this->makeEntry('e')->save();

        $this->assertCount(4, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['sub_title:is_empty' => true]));
        $this->assertCount(3, $this->getEntries(['sub_title:is_blank' => true]));
        $this->assertCount(1, $this->getEntries(['sub_title:is_empty' => false]));
        $this->assertCount(1, $this->getEntries(['sub_title:is_blank' => false]));

        // Non-conventional `is_` conditions for backwards compatibility...
        $this->assertCount(3, $this->getEntries(['sub_title:doesnt_exist' => true]));
        $this->assertCount(1, $this->getEntries(['sub_title:doesnt_exist' => false]));
        $this->assertCount(3, $this->getEntries(['sub_title:not_set' => true]));
        $this->assertCount(1, $this->getEntries(['sub_title:not_set' => false]));
        $this->assertCount(3, $this->getEntries(['sub_title:isnt_set' => true]));
        $this->assertCount(1, $this->getEntries(['sub_title:isnt_set' => false]));
        $this->assertCount(3, $this->getEntries(['sub_title:null' => true]));
        $this->assertCount(1, $this->getEntries(['sub_title:null' => false]));
        $this->assertCount(1, $this->getEntries(['sub_title:exists' => true]));
        $this->assertCount(3, $this->getEntries(['sub_title:exists' => false]));
        $this->assertCount(1, $this->getEntries(['sub_title:isset' => true]));
        $this->assertCount(3, $this->getEntries(['sub_title:isset' => false]));
    }

    #[Test]
    public function it_filters_by_is_numberwang_condition()
    {
        $this->makeEntry('a')->set('age', 22)->save();
        $this->makeEntry('b')->set('age', 57)->save();
        $this->makeEntry('c')->set('age', 2.3)->save();

        $this->assertCount(3, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['age:is_numberwang' => true]));
        $this->assertCount(1, $this->getEntries(['age:is_numberwang' => false]));
    }

    #[Test]
    public function when_the_value_is_an_augmentable_object_it_will_use_the_corresponding_value()
    {
        // The value doesn't have to be an entry, it just has to be an augmentable.
        // It's just simple for us to create an entry here.
        $value = Facades\Entry::make()
            ->collection(Facades\Collection::make('test'))
            ->set('somefield', 'somevalue');

        $class = new class($value)
        {
            use QueriesConditions;
            protected $params;

            public function __construct($value)
            {
                $this->params = new Parameters(['somefield:is' => $value]);
            }

            public function query($query)
            {
                $this->queryConditions($query);
            }
        };

        $query = $this->mock(Builder::class);
        $query->shouldReceive('where')->with('somefield', 'somevalue')->once();

        $class->query($query);
    }

    #[Test]
    public function when_the_value_is_an_array_of_augmentables_it_will_get_the_respective_values()
    {
        // The value doesn't have to be an entry, it just has to be an augmentable.
        // It's just simple for us to create an entry here.
        $value = Facades\Entry::make()
            ->collection(Facades\Collection::make('test'))
            ->set('somefield', 'somevalue');

        $values = [$value];

        $class = new class($values)
        {
            use QueriesConditions;
            protected $params;

            public function __construct($values)
            {
                $this->params = new Parameters(['somefield:in' => $values]);
            }

            public function query($query)
            {
                $this->queryConditions($query);
            }
        };

        $query = $this->mock(Builder::class);
        $query->shouldReceive('whereIn')->with('somefield', ['somevalue'])->once();

        $class->query($query);
    }

    #[Test]
    public function when_the_value_is_a_collection_of_augmentables_it_will_get_the_respective_values()
    {
        // The value doesn't have to be an entry, it just has to be an augmentable.
        // It's just simple for us to create an entry here.
        $value = Facades\Entry::make()
            ->collection(Facades\Collection::make('test'))
            ->set('somefield', 'somevalue');

        $values = collect([$value]);

        $class = new class($values)
        {
            use QueriesConditions;
            protected $params;

            public function __construct($values)
            {
                $this->params = new Parameters(['somefield:in' => $values]);
            }

            public function query($query)
            {
                $this->queryConditions($query);
            }
        };

        $query = $this->mock(Builder::class);
        $query->shouldReceive('whereIn')->with('somefield', ['somevalue'])->once();

        $class->query($query);
    }

    #[Test]
    public function when_the_value_is_a_labeled_value_object_it_will_use_the_corresponding_value()
    {
        $value = new LabeledValue('foo', 'The Foo Label');

        $class = new class($value)
        {
            use QueriesConditions;
            protected $params;

            public function __construct($value)
            {
                $this->params = new Parameters(['somefield:is' => $value]);
            }

            public function query($query)
            {
                $this->queryConditions($query);
            }
        };

        $query = $this->mock(Builder::class);
        $query->shouldReceive('where')->with('somefield', 'foo')->once();

        $class->query($query);
    }

    #[Test]
    public function when_the_value_is_a_non_augmentable_object_it_will_throw_an_exception()
    {
        $this->expectExceptionMessage('Cannot query [somefield] using value [Tests\Tags\Concerns\SomeArbitraryTestObject]');

        $value = new SomeArbitraryTestObject;

        $class = new class($value)
        {
            use QueriesConditions;
            protected $params;

            public function __construct($value)
            {
                $this->params = new Parameters(['somefield:is' => $value]);
            }

            public function query($query)
            {
                $this->queryConditions($query);
            }
        };

        $query = $this->mock(Builder::class);
        $query->shouldReceive('where')->never();

        $class->query($query);
    }
}

class SomeArbitraryTestObject
{
    //
}
