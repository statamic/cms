<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
use Statamic\Facades\Site;
use Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LocalesTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutEvents();

        Site::setConfig(['sites' => [
            'en' => ['url' => '/en', 'name' => 'English', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr', 'name' => 'French', 'locale' => 'fr_FR'],
            'es' => ['url' => '/es', 'name' => 'Spanish', 'locale' => 'es_ES'],
        ]]);
    }

    private function tag($tag, $context = [])
    {
        return (string) Parse::template($tag, $context);
    }

    /** @test */
    public function it_loops_over_the_entry_for_each_site()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('en')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('fr')
            ->id('2')
            ->origin('1')
            ->data(['title' => 'bonjour'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('es')
            ->id('3')
            ->origin('1')
            ->data(['title' => 'hola'])
            ->create();

        $this->assertEquals(
            '<hello><bonjour><hola>',
            $this->tag('{{ locales }}<{{ title }}>{{ /locales }}', ['id' => '1'])
        );
    }

    /** @test */
    public function it_skips_a_site_in_the_loop_if_the_entry_doesnt_exist()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('en')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('es')
            ->id('3')
            ->origin('1')
            ->data(['title' => 'hola'])
            ->create();

        $this->assertEquals(
            '<hello><hola>',
            $this->tag('{{ locales }}<{{ title }}>{{ /locales }}', ['id' => '1'])
        );
    }

    /** @test */
    public function it_skips_a_site_in_the_loop_if_the_entry_is_a_draft()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('en')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('fr')
            ->id('2')
            ->origin('1')
            ->data(['title' => 'bonjour'])
            ->published(false)
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('es')
            ->id('3')
            ->origin('1')
            ->data(['title' => 'hola'])
            ->create();

        $this->assertEquals(
            '<hello><hola>',
            $this->tag('{{ locales }}<{{ title }}>{{ /locales }}', ['id' => '1'])
        );
    }

    /** @test */
    public function it_shows_the_entry_in_a_given_site()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('en')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('es')
            ->id('3')
            ->origin('1')
            ->data(['title' => 'hola'])
            ->create();

        $this->assertEquals(
            '<hola>',
            $this->tag('{{ locales:es }}<{{ title }}>{{ /locales:es }}', ['id' => '1'])
        );
    }

    /** @test */
    public function it_shows_nothing_if_the_entry_doesnt_exist_in_a_given_site()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('en')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();

        $this->assertEquals(
            '',
            $this->tag('{{ locales:es }}<{{ title }}>{{ /locales:es }}', ['id' => '1'])
        );
    }
}
