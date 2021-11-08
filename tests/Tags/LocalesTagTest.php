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

    private function template($tag)
    {
        $contents = <<<EOT
$tag
- {{ id }}
- {{ title }}
- {{ locale:name }}
- {{ locale:handle }}
- {{ locale:short }}
- {{ current }}
- {{ is_current ? 'current' : 'not current' }}

{{ /locales }}
EOT;

        return $contents;
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutEvents();

        Site::setConfig(['sites' => [
            'english' => ['url' => '/en', 'name' => 'English', 'locale' => 'en_US'],
            'french' => ['url' => '/fr', 'name' => 'French', 'locale' => 'fr_FR'],
            'espanol' => ['url' => '/es', 'name' => 'Spanish', 'locale' => 'es_ES'],
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
            ->locale('english')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('french')
            ->id('2')
            ->origin('1')
            ->data(['title' => 'bonjour'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('espanol')
            ->id('3')
            ->origin('1')
            ->data(['title' => 'hola'])
            ->create();

        $expected = <<<'HTML'
- 1
- hello
- English
- english
- en
- english
- current

- 2
- bonjour
- French
- french
- fr
- english
- not current

- 3
- hola
- Spanish
- espanol
- es
- english
- not current


HTML;

        $this->assertEquals($expected, $this->tag($this->template('{{ locales }}'), ['id' => '1']));
    }

    /** @test */
    public function it_skips_a_site_in_the_loop_if_the_entry_doesnt_exist()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('english')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('espanol')
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
    public function it_falls_back_to_the_sites_details_if_the_entry_doesnt_exist_and_the_all_param_is_used()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('english')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('espanol')
            ->id('3')
            ->origin('1')
            ->data(['title' => 'hola'])
            ->create();

        $expected = <<<'HTML'
- 1
- hello
- English
- english
- en
- english
- current

-
-
- French
- french
- fr
- english
- not current

- 3
- hola
- Spanish
- espanol
- es
- english
- not current


HTML;

        $this->assertEquals($expected, $this->tag($this->template('{{ locales all="true" }}'), ['id' => '1']));
    }

    /** @test */
    public function it_skips_a_site_in_the_loop_if_the_entry_is_a_draft()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('english')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('french')
            ->id('2')
            ->origin('1')
            ->data(['title' => 'bonjour'])
            ->published(false)
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('espanol')
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
    public function it_falls_back_to_the_sites_details_if_the_entry_is_a_draft_and_the_all_param_is_used()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('english')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('french')
            ->id('2')
            ->origin('1')
            ->data(['title' => 'bonjour'])
            ->published(false)
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('espanol')
            ->id('3')
            ->origin('1')
            ->data(['title' => 'hola'])
            ->create();

        $expected = <<<'HTML'
- 1
- hello
- English
- english
- en
- english
- current

-
-
- French
- french
- fr
- english
- not current

- 3
- hola
- Spanish
- espanol
- es
- english
- not current


HTML;

        $this->assertEquals($expected, $this->tag($this->template('{{ locales all="true" }}'), ['id' => '1']));
    }

    /** @test */
    public function it_shows_the_entry_in_a_given_site()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('english')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();
        (new EntryFactory)
            ->collection('test')
            ->locale('espanol')
            ->id('3')
            ->origin('1')
            ->data(['title' => 'hola'])
            ->create();

        $this->assertEquals(
            '<hola>',
            $this->tag('{{ locales:espanol }}<{{ title }}>{{ /locales:espanol }}', ['id' => '1'])
        );
    }

    /** @test */
    public function it_shows_nothing_if_the_entry_doesnt_exist_in_a_given_site()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('english')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();

        $this->assertEquals(
            '',
            $this->tag('{{ locales:espanol }}<{{ title }}>{{ /locales:espanol }}', ['id' => '1'])
        );
    }
}
