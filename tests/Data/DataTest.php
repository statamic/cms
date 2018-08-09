<?php

namespace Tests\Data;

use Statamic\Data\Entries\Entry as EntryObj;
use Tests\TestCase;
use Statamic\API\Entry;

class DataTest extends TestCase
{
    /** @var  EntryObj */
    protected $page;

    public function setUp()
    {
        parent::setUp();

        $this->page = Entry::create('about')
            ->collection('pages')
            ->path('collections/pages/about/index.md')
            ->with([
                'title' => 'Test',
                'foo' => 'bar',
            ])->get();
    }

    public function testSetsAndGetsData()
    {
        $data = ['foo' => 'bar', 'bar' => 'baz'];
        $this->page->data($data);
        $this->assertEquals($data, $this->page->data());
    }

    public function testSetsAndGetsDataKeys()
    {
        $this->page->set('baz', 'qux');
        $this->assertEquals('qux', $this->page->get('baz'));
    }

    public function testRemovesData()
    {
        $this->page->remove('foo');
        $this->assertArrayNotHasKey('foo', $this->page->data());
    }

    public function test_that_methods_are_chainable()
    {
        $this->assertInstanceOf(EntryObj::class, $this->page->set('foo', 'bar'));
        $this->assertInstanceOf(EntryObj::class, $this->page->remove('foo'));
        $this->assertInstanceOf(EntryObj::class, $this->page->data([]));
        $this->assertInstanceOf(EntryObj::class, $this->page->syncOriginal());
        $this->assertInstanceOf(EntryObj::class, $this->page->dataType('md'));
        $this->assertInstanceOf(EntryObj::class, $this->page->content('foo'));
        // $this->assertInstanceOf(EntryObj::class, $this->page->path('foo')); // @TODO ?
        $this->assertInstanceOf(EntryObj::class, $this->page->id('foo'));
        $this->assertInstanceOf(EntryObj::class, $this->page->setSupplement('foo', 'bar'));
        $this->assertInstanceOf(EntryObj::class, $this->page->removeSupplement('foo', 'bar'));
    }

    public function testEnsuresUuid()
    {
        $this->assertNull($this->page->id());
        $this->page->ensureId();
        $this->assertNotNull($this->page->id());
    }

    public function testGetsAndSetsUuid()
    {
        $this->assertNull($this->page->id());
        $this->page->id('123');
        $this->assertEquals('123', $this->page->id());
    }

    public function testGetsExtension()
    {
        $this->assertEquals('md', $this->page->dataType());
    }

    public function testSupplements()
    {
        $this->page->setSupplement('one', 'uno');
        $this->page->setSupplement('two', 'dos');
        $this->assertEquals('uno', $this->page->getSupplement('one'));
        $this->page->removeSupplement('one');
        $this->assertArrayNotHasKey('one', $this->page->supplements());
    }

    public function testParsesContentForMarkdown()
    {
        $this->page->dataType('md');
        $this->page->content('# Foo');
        $this->assertEquals('<h1>Foo</h1>', trim($this->page->parseContent()));
    }

    public function testParsesContentForTextile()
    {
        $this->page->dataType('textile');
        $this->page->content('h1. Foo');
        $this->assertEquals('<h1>Foo</h1>', trim($this->page->parseContent()));
    }

    public function testParsesContentForText()
    {
        $this->page->dataType('txt');
        $this->page->content('<p>Foo</p>');
        $this->assertEquals('Foo', trim($this->page->parseContent()));
    }

    public function testParsesContentAndIgnoresTags()
    {
        $this->page->set('parse_content', false);
        $this->page->content('{{ foo }}');
        $this->assertStringStartsWith('<p>&lbrace;', trim($this->page->parseContent()));
    }


    public function test_that_localized_keys_can_be_get_set_and_removed()
    {
        $this->page->set('hello', 'Hello');
        $this->assertEquals('Hello', $this->page->get('hello'));
        $this->page->remove('hello');
        $this->assertNull($this->page->get('hello'));

        $this->page->in('fr')->set('hello', 'Bonjour');
        $this->assertEquals('Bonjour', $this->page->in('fr')->get('hello'));
        $this->page->in('fr')->remove('hello');
        $this->assertNull($this->page->in('fr')->get('hello'));
    }

    public function test_that_all_localized_data_can_be_retrieved()
    {
        $this->page->data([]); // clean slate

        $this->page->set('hello', 'Hello');
        $this->page->set('world', 'World');

        $this->page->in('fr')->set('hello', 'Bonjour')
                             ->set('world', 'Monde');

        $this->assertEquals(
            ['hello' => 'Bonjour', 'world' => 'Monde'],
            $this->page->in('fr')->data()
        );

        $this->assertEquals(
            ['hello' => 'Hello', 'world' => 'World'],
            $this->page->data()
        );
    }

    public function test_that_localized_data_falls_back_to_default_locale()
    {
        $this->page->data([]); // clean slate

        $this->page->set('hello', 'Hello');
        $this->page->set('world', 'World');

        $this->page->in('fr')->set('hello', 'Bonjour');

        $this->assertEquals('Bonjour', $this->page->in('fr')->get('hello'));
        $this->assertNull($this->page->in('fr')->get('world'));
        $this->assertEquals('World', $this->page->in('fr')->getWithCascade('world'));

        $this->assertEquals(
            ['hello' => 'Bonjour', 'world' => 'World'],
            $this->page->in('fr')->dataWithDefaultLocale()
        );
    }
}
