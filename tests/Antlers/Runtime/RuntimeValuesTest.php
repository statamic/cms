<?php

namespace Tests\Antlers\Runtime;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Entries\Entry;
use Statamic\Facades;
use Statamic\Facades\Collection;
use Statamic\Facades\GlobalSet;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class RuntimeValuesTest extends ParserTestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function test_supplemented_values_are_not_cached()
    {
        $this->withFakeViews();

        $instance = (new class extends Tags
        {
            public static $handle = 'dont_cache';

            public function mePlease()
            {
                $entry = Entry::find('2');

                $supplements = ['one', 'two'];

                return collect($supplements)
                    ->map(function (string $supplement, $key) use ($entry) {
                        return unserialize(serialize($entry))->setSupplement('foo', $supplement);
                    });
            }
        });

        $instance::register();

        Collection::make('pages')->routes(['en' => '{slug}'])->save();
        EntryFactory::collection('pages')->id('1')->slug('home')->data(['title' => 'Home'])->create();
        EntryFactory::collection('pages')->id('2')->slug('about')->data(['title' => 'About'])->create();

        $template = <<<'EOT'
{{ title }}

{{ dont_cache:me_please }}{{ foo }}{{ /dont_cache:me_please }}
EOT;

        $this->viewShouldReturnRaw('default', $template);
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');

        $response = $this->get('home')->assertOk();
        $content = StringUtilities::normalizeLineEndings($response->content());

        $expected = <<<'EXPECTED'
Home

onetwo
EXPECTED;

        $this->assertSame($expected, $content);
    }

    public function test_fieldtype_information_is_resolved_when_augmenting()
    {
        // https://github.com/statamic/cms/issues/10001

        $blueprint = Facades\Blueprint::makeFromFields([
            'the_text' => ['type' => 'text', 'antlers' => true],
        ]);

        BlueprintRepository::shouldReceive('find')->with('globals.the_global')->andReturn($blueprint);

        $global = GlobalSet::make('the_global');
        $variables = $global->in('en');

        $variables->set('the_text', 'The Value');
        $theText = $variables->toDeferredAugmentedArray()['the_text'];

        $this->assertTrue($theText->shouldParseAntlers());
    }
}
