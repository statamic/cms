<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Parse;
use Statamic\Tags\Children;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ChildrenTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $collection;

    public function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    private function setUpEntries()
    {
        $this->collection = tap(Collection::make('pages')->sites(['en', 'fr'])->routes('{slug}')->structureContents(['root' => false]))->save();

        EntryFactory::collection('pages')->id('foo')->data([
            'title' => 'the foo entry',
        ])->create();

        EntryFactory::collection('pages')->id('bar')->data([
            'title' => 'the bar entry',
        ])->create();

        EntryFactory::collection('pages')->id('baz')->data([
            'title' => 'the baz entry',
        ])->create();

        EntryFactory::collection('pages')->id('fr-foo')->origin('foo')->locale('fr')->data([
            'title' => 'the french foo entry',
        ])->create();

        EntryFactory::collection('pages')->id('fr-bar')->origin('foo')->locale('fr')->data([
            'title' => 'the french bar entry',
        ])->create();

        EntryFactory::collection('pages')->id('fr-baz')->origin('foo')->locale('fr')->data([
            'title' => 'the french baz entry',
        ])->create();

        $this->collection->structure()->in('en')->tree([
            ['entry' => 'foo', 'url' => '/foo', 'children' => [
                ['entry' => 'bar', 'url' => '/foo/bar', 'children' => [
                    ['entry' => 'baz', 'url' => '/foo/bar/baz'],
                ]],
            ]],
        ])->save();

        $this->collection->structure()->in('fr')->tree([
            ['entry' => 'fr-foo', 'url' => '/fr-foo', 'children' => [
                ['entry' => 'fr-bar', 'url' => '/fr-foo/fr-bar', 'children' => [
                    ['entry' => 'fr-baz', 'url' => '/fr-foo/fr-bar/fr-baz'],
                ]],
            ]],
        ])->save();
    }

    #[Test]
    public function it_gets_children_data()
    {
        $this->setUpEntries();
        $this->get('/foo');

        $this->assertEquals('the bar entry', $this->tag('{{ children }}{{ title }}{{ /children }}', ['collection' => $this->collection]));
    }

    #[Test]
    public function it_gets_children_data_of_another_entry()
    {
        $this->setUpEntries();

        $this->get('/foo');

        $this->assertEquals('the baz entry', $this->tag('{{ children of="/foo/bar" }}{{ title }}{{ /children }}', ['collection' => $this->collection]));
    }

    #[Test]
    public function it_gets_children_data_when_in_another_site()
    {
        $this->setUpEntries();

        $this->get('/fr/fr-foo');

        $this->assertEquals('the french bar entry', $this->tag('{{ children }}{{ title }}{{ /children }}', ['collection' => $this->collection]));
    }

    #[Test]
    public function it_doesnt_affect_children_in_nav()
    {
        $this->setUpEntries();

        $mock = Mockery::mock(Children::class);
        $mock->shouldNotReceive('index');

        $this->app['statamic.tags']['children'] = $mock;

        $this->get('/foo');

        $this->assertEquals('the bar entry', $this->tag('{{ nav }}{{ children }}{{ title }}{{ /children }}{{ /nav }}'));
    }
}
