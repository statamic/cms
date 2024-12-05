<?php

namespace Tests\Tags;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Parse;
use Statamic\Fields\Value;
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
-{{ id }}
-{{ title }}
-{{ url }}
-{{ permalink }}
-{{ exists ? 'exists' : 'does not exist' }}
-{{ locale:name }}
-{{ locale:handle }}
-{{ locale:key }}
-{{ locale:short }}
-{{ locale:full }}
-{{ locale:url }}
-{{ locale:permalink }}
-{{ current }}
-{{ is_current ? 'current' : 'not current' }}

{{ /locales }}
EOT;

        return $contents;
    }

    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->setSites([
            'english' => ['url' => '/en', 'name' => 'English', 'locale' => 'en_US'],
            'french' => ['url' => '/fr', 'name' => 'French', 'locale' => 'fr_FR'],
            'espanol' => ['url' => '/es', 'name' => 'Spanish', 'locale' => 'es_ES'],
        ]);

        Collection::make('test')
            ->routes('{id}')
            ->sites(['english', 'french', 'espanol'])
            ->save();
    }

    private function tag($tag, $context = [])
    {
        return (string) Parse::template($tag, $context);
    }

    #[Test]
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

-1
-hello
-/en/1
-http://localhost/en/1
-exists
-English
-english
-english
-en
-en_US
-/en
-http://localhost/en
-english
-current


-2
-bonjour
-/fr/2
-http://localhost/fr/2
-exists
-French
-french
-french
-fr
-fr_FR
-/fr
-http://localhost/fr
-english
-not current


-3
-hola
-/es/3
-http://localhost/es/3
-exists
-Spanish
-espanol
-espanol
-es
-es_ES
-/es
-http://localhost/es
-english
-not current


HTML;

        $this->assertEquals($expected, $this->tag($this->template('{{ locales }}'), ['id' => '1']));
    }

    #[Test]
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

    #[Test]
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

-1
-hello
-/en/1
-http://localhost/en/1
-exists
-English
-english
-english
-en
-en_US
-/en
-http://localhost/en
-english
-current


-
-
-/fr
-http://localhost/fr
-does not exist
-French
-french
-french
-fr
-fr_FR
-/fr
-http://localhost/fr
-english
-not current


-3
-hola
-/es/3
-http://localhost/es/3
-exists
-Spanish
-espanol
-espanol
-es
-es_ES
-/es
-http://localhost/es
-english
-not current


HTML;

        $this->assertEquals($expected, $this->tag($this->template('{{ locales all="true" }}'), ['id' => '1']));
    }

    #[Test]
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

    #[Test]
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

-1
-hello
-/en/1
-http://localhost/en/1
-exists
-English
-english
-english
-en
-en_US
-/en
-http://localhost/en
-english
-current


-
-
-/fr
-http://localhost/fr
-does not exist
-French
-french
-french
-fr
-fr_FR
-/fr
-http://localhost/fr
-english
-not current


-3
-hola
-/es/3
-http://localhost/es/3
-exists
-Spanish
-espanol
-espanol
-es
-es_ES
-/es
-http://localhost/es
-english
-not current


HTML;

        $this->assertEquals($expected, $this->tag($this->template('{{ locales all="true" }}'), ['id' => '1']));
    }

    #[Test]
    public function it_skips_its_own_locale_when_self_param_is_false()
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

        $this->assertEquals(
            '<bonjour><hola>',
            $this->tag('{{ locales self="false" }}<{{ title }}>{{ /locales }}', ['id' => '1'])
        );
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_displays_nothing_when_there_are_no_results()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('english')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();

        $this->assertEquals(
            '',
            $this->tag('{{ locales self="false" }}you should not see this{{ /locales }}', ['id' => '1'])
        );
    }

    #[Test]
    public function it_displays_nothing_when_context_id_is_null()
    {
        $entry = (new EntryFactory)
            ->collection('test')
            ->locale('english')
            ->data(['title' => 'hello'])
            ->make();

        $value = new Value(null, 'id', null, $entry);

        $this->assertEquals(
            '',
            $this->tag('{{ locales }}you should not see this{{ /locales }}', ['id' => $value])
        );
    }

    #[Test]
    public function it_prefers_page_id_over_id()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('english')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();

        $this->assertEquals(
            '<hello>',
            $this->tag('{{ locales }}<{{ title }}>{{ /locales }}', ['page' => ['id' => '1']])
        );
    }

    #[Test]
    public function it_prefers_id_param_over_page_id()
    {
        (new EntryFactory)
            ->collection('test')
            ->locale('english')
            ->id('1')
            ->data(['title' => 'hello'])
            ->create();

        $this->assertEquals(
            '<hello>',
            $this->tag('{{ locales id="1" }}<{{ title }}>{{ /locales }}', ['page' => ['id' => '7']])
        );
    }
}
