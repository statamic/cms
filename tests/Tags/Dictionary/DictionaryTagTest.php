<?php

namespace Tests\Tags\Dictionary;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Query\Scopes\Scope;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DictionaryTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_countries()
    {
        $template = '{{ dictionary:countries limit="1" }}{{ value }}{{ /dictionary:countries }}';

        $this->assertEquals('AFG', $this->tag($template));
    }

    #[Test]
    public function it_gets_dictionary_by_handle()
    {
        $template = '{{ dictionary handle="countries" limit="1" }}{{ value }}{{ /dictionary }}';

        $this->assertEquals('AFG', $this->tag($template));
    }

    #[Test]
    public function it_gets_timezones()
    {
        $template = '{{ dictionary:timezones limit="1" }}{{ value }}{{ /dictionary:timezones }}';

        $this->assertEquals('Africa/Abidjan', $this->tag($template));
    }

    #[Test]
    public function it_can_search()
    {
        $template = '{{ dictionary:countries search="Alg" }}{{ value }}{{ /dictionary:countries }}';

        $this->assertEquals('DZA', $this->tag($template));
    }

    #[Test]
    public function it_pulls_extra_data_data()
    {
        $template = '{{ dictionary:countries search="Alg" }}{{ region }} - {{ iso2 }}{{ /dictionary:countries }}';

        $this->assertEquals('Africa - DZ', $this->tag($template));
    }

    #[Test]
    public function it_can_paginate()
    {
        $template = '{{ dictionary:countries paginate="4" }}{{ options | count }}{{ /dictionary:countries }}';

        $this->assertEquals('4', $this->tag($template));

        $template = '{{ dictionary:countries paginate="4" as="countries" }}{{ countries | count }}{{ /dictionary:countries }}';

        $this->assertEquals('4', $this->tag($template));
    }

    #[Test]
    public function it_can_be_filtered_using_conditions()
    {
        $template = '{{ dictionary:countries iso3:is="AUS" }}{{ name }}{{ /dictionary:countries }}';

        $this->assertEquals('Australia', $this->tag($template));
    }

    #[Test]
    public function it_can_be_filtered_using_a_query_scope()
    {
        app('statamic.scopes')[TestScope::handle()] = TestScope::class;

        $template = '{{ dictionary:countries query_scope="test_scope" }}{{ name }}{{ /dictionary:countries }}';

        $this->assertEquals('Australia', $this->tag($template));
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }
}

class TestScope extends Scope
{
    public function apply($query, $params)
    {
        $query->where('iso3', 'AUS');
    }
}
