<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Parse;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ParentTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

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
        Collection::make('pages')->routes('{slug}')->sites(['en', 'fr'])->save();

        EntryFactory::collection('pages')->id('foo')->slug('foo')->data([
            'title' => 'the foo entry',
            'arr' => ['a' => 'alfa', 'b' => 'bravo'],
        ])->create();

        EntryFactory::collection('pages')->id('fr-foo')->origin('foo')->locale('fr')->slug('foo')->data([
            'title' => 'the french foo entry',
            'arr' => ['a' => 'le-alfa', 'b' => 'le-bravo'],
        ])->create();
    }

    #[Test]
    public function it_gets_the_parent_data()
    {
        $this->setUpEntries();

        $this->get('/foo/bar');

        $this->assertEquals('/foo', $this->tag('{{ parent }}'));
        $this->assertEquals('the foo entry', $this->tag('{{ parent:title }}'));
        $this->assertEquals('the foo entry', $this->tag('{{ parent }}{{ title }}{{ /parent }}'));
        $this->assertEquals('<alfa><bravo>', $this->tag('{{ parent:arr }}<{{ a }}><{{ b }}>{{ /parent:arr }}'));
    }

    #[Test]
    public function it_gets_the_parent_data_when_in_another_site()
    {
        $this->setUpEntries();

        $this->get('/fr/foo/bar');

        $this->assertEquals('/fr/foo', $this->tag('{{ parent }}'));
        $this->assertEquals('the french foo entry', $this->tag('{{ parent:title }}'));
        $this->assertEquals('the french foo entry', $this->tag('{{ parent }}{{ title }}{{ /parent }}'));
        $this->assertEquals('<le-alfa><le-bravo>', $this->tag('{{ parent:arr }}<{{ a }}><{{ b }}>{{ /parent:arr }}'));
    }
}
