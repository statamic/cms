<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Antlers;
use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GetContentTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $one;
    private $two;

    public function setUp(): void
    {
        parent::setUp();

        Collection::make('blog')->routes([
            'en' => '/blog/{slug}',
            'fr' => '/le-blog/{slug}',
        ])->save();

        $this->one = EntryFactory::id('123')->collection('blog')->slug('first')->data(['title' => 'First'])->create();
        $this->two = EntryFactory::id('456')->collection('blog')->slug('second')->data(['title' => 'Second'])->create();
    }

    #[Test]
    public function it_gets_single_item_by_id()
    {
        $this->assertParseEquals('First', '{{ get_content from="123" }}{{ title }}{{ /get_content }}');
    }

    #[Test]
    public function it_gets_single_item_by_uri()
    {
        $this->assertParseEquals('First', '{{ get_content from="/blog/first" }}{{ title }}{{ /get_content }}');
    }

    #[Test]
    public function it_gets_single_item_by_variable()
    {
        $this->assertParseEquals(
            'First',
            '{{ get_content :from="id" }}{{ title }}{{ /get_content }}',
            ['id' => '123']
        );
    }

    #[Test]
    public function it_gets_multiple_items_by_pipe_delimited_ids()
    {
        $this->assertParseEquals(
            '<First><Second>',
            '{{ get_content from="123|456" }}<{{ title }}>{{ /get_content }}'
        );
    }

    #[Test]
    public function it_gets_multiple_items_by_pipe_delimited_uris()
    {
        $this->assertParseEquals(
            '<First><Second>',
            '{{ get_content from="/blog/first|/blog/second" }}<{{ title }}>{{ /get_content }}'
        );
    }

    #[Test]
    public function it_gets_multiple_items_by_variable_containing_ids()
    {
        $this->assertParseEquals(
            '<First><Second>',
            '{{ get_content :from="ids" }}<{{ title }}>{{ /get_content }}',
            ['ids' => ['123', '456']]
        );
    }

    #[Test]
    public function it_gets_multiple_items_by_variable_containing_uris()
    {
        $this->assertParseEquals(
            '<First><Second>',
            '{{ get_content :from="uris" }}<{{ title }}>{{ /get_content }}',
            ['uris' => ['/blog/first', '/blog/second']]
        );
    }

    #[Test]
    public function it_gets_single_item_using_shorthand()
    {
        $this->assertParseEquals(
            'First',
            '{{ get_content:foo }}{{ title }}{{ /get_content:foo }}',
            ['foo' => '123']
        );
    }

    #[Test]
    public function it_gets_multiple_items_by_pipe_delimited_ids_using_shorthand()
    {
        $this->assertParseEquals(
            '<First><Second>',
            '{{ get_content:foo }}<{{ title }}>{{ /get_content:foo }}',
            ['foo' => '123|456']
        );
    }

    #[Test]
    public function it_gets_multiple_items_by_pipe_delimited_uris_using_shorthand()
    {
        $this->assertParseEquals(
            '<First><Second>',
            '{{ get_content:foo }}<{{ title }}>{{ /get_content:foo }}',
            ['foo' => '/blog/first|/blog/second']
        );
    }

    #[Test]
    public function it_gets_multiple_items_by_variable_containing_ids_using_shorthand()
    {
        $this->assertParseEquals(
            '<First><Second>',
            '{{ get_content:foo }}<{{ title }}>{{ /get_content:foo }}',
            ['foo' => ['123', '456']]
        );
    }

    #[Test]
    public function it_gets_multiple_items_by_variable_containing_uris_using_shorthand()
    {
        $this->assertParseEquals(
            '<First><Second>',
            '{{ get_content:foo }}<{{ title }}>{{ /get_content:foo }}',
            ['foo' => ['/blog/first', '/blog/second']]
        );
    }

    #[Test]
    public function it_returns_the_entries_if_theyre_already_entries()
    {
        $this->assertParseEquals(
            '<First><Second>',
            '{{ get_content :from="foo" }}<{{ title }}>{{ /get_content }}',
            ['foo' => EntryCollection::make([$this->one, $this->two])]
        );
    }

    #[Test]
    public function it_returns_the_entries_if_theyre_already_entries_using_shorthand()
    {
        $this->assertParseEquals(
            '<First><Second>',
            '{{ get_content:foo }}<{{ title }}>{{ /get_content:foo }}',
            ['foo' => EntryCollection::make([$this->one, $this->two])]
        );
    }

    private function assertParseEquals($expected, $template, $context = [])
    {
        $this->assertEquals($expected, (string) Antlers::parse($template, $context));
    }
}
