<?php

namespace Tests\Antlers\Runtime;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Entries\Collection;
use Statamic\Modifiers\Modifier;
use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class EnvironmentDetailsTest extends ParserTestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function test_registered_tags_and_modifiers_are_available()
    {
        Collection::make('pages')->routes('{slug}')->save();
        EntryFactory::collection('pages')->id('1')->data(['title' => 'The Title', 'content' => 'The content'])->slug('/')->create();

        (new class extends Tags
        {
            public static $handle = 'the_tag';

            public function index()
            {
                return 'The Tag!';
            }
        })::register();

        (new class extends Modifier
        {
            protected static $handle = 'the_modifier';

            public function index($value, $params, $context)
            {
                return mb_strtoupper($value);
            }
        })::register();

        $layout = <<<'LAYOUT'
{{ template_content }}
LAYOUT;
        $default = <<<'DEFAULT'
{{ the_tag /}}{{ title | the_modifier /}}
DEFAULT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', $layout);
        $this->viewShouldReturnRaw('default', $default);

        $responseOne = $this->get('/')->assertOk();
        $content = trim($responseOne->content());

        $this->assertSame('The Tag!THE TITLE', $content);
    }
}
