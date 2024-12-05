<?php

namespace Tests;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Fieldtype;
use Statamic\View\View;

class MiscTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    /**
     * @see https://github.com/statamic/cms/issues/4839
     **/
    #[Test]
    #[DataProvider('localesTagTestProvider')]
    public function locales_tag_doesnt_ruin_future_tag_pairs($withParameter)
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'de' => ['url' => 'http://localhost/de/', 'locale' => 'de', 'name' => 'German'],
        ]);

        $blueprint = Blueprint::makeFromFields(['related_entries' => ['type' => 'entries']]);
        Blueprint::shouldReceive('in')->with('collections/test')->andReturn(collect([$blueprint]));

        // create some entries that will be used in another entry's relationship field.
        $relatedA = EntryFactory::collection('test')->id('related-a')->slug('related-a')->locale('en')->data(['title' => 'Foo'])->create();
        $relatedB = EntryFactory::collection('test')->id('related-b')->slug('related-b')->locale('en')->data(['title' => 'Bar'])->create();
        $relatedAInGerman = EntryFactory::collection('test')->id('related-a-in-de')->slug('related-a-in-de')->locale('de')->origin('related-a')->data(['title' => 'Das Foo'])->create();
        $relatedBInGerman = EntryFactory::collection('test')->id('related-b-in-de')->slug('related-b-in-de')->locale('de')->origin('related-b')->data(['title' => 'Das Bar'])->create();

        // create an entry with a relationship field that references the above entries.
        $a = EntryFactory::collection('test')->id('a')->slug('a')->locale('en')->data(['related_entries' => ['related-a', 'related-b']])->create();
        $b = EntryFactory::collection('test')->id('b')->slug('b')->locale('de')->origin('a')->create();

        // The locales tag would be setting the field's parent during augmentation. The last iteration would stick around.
        // Then when the relationship field is looped over, it uses whatever the last item in the locales tag was.
        // But this only happened if the relationship field was used without a parameter.
        $param = $withParameter ? 'limit="3"' : '';
        $template = <<<EOT
Locales: {{ locales }}<{{ locale:handle }}:{{ id }}>{{ /locales }}
Entries: {{ related_entries $param }}<{{ title }}>{{ /related_entries }}
EOT;

        $expected = <<<'EOT'
Locales: <en:a><de:b>
Entries: <Foo><Bar>
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('test', $template);
        $this->assertEquals($expected, View::make('test')->cascadeContent($a)->render());
    }

    public static function localesTagTestProvider()
    {
        return [
            'without parameter' => [false],
            'with parameter' => [true],
        ];
    }

    /**
     * @see https://github.com/statamic/cms/issues/4889
     **/
    #[Test]
    public function fieldtype_gets_correct_parent_in_loop()
    {
        $fieldtype = new class extends Fieldtype
        {
            public static function handle()
            {
                return 'custom';
            }

            public function augment($value)
            {
                // This would end up being the title of the last item in the loop, for all entries.
                $title = $this->field()->parent()->value('title');

                return 'custom_'.$title;
            }
        };

        $fieldtype::register();

        $blueprint = Blueprint::makeFromFields(['custom' => ['type' => 'custom']]);
        Blueprint::shouldReceive('in')->with('collections/test')->andReturn(collect([$blueprint]));

        EntryFactory::collection('test')->id('a')->slug('a')->data(['title' => 'one'])->create();
        EntryFactory::collection('test')->id('b')->slug('b')->data(['title' => 'two'])->create();

        $this->withFakeViews();
        $this->viewShouldReturnRaw('test', '{{ collection:test }}<{{ title }}:{{ custom }}>{{ /collection:test }}');

        // This would be '<one:custom_two><two:custom_two>' with the bug.
        $expected = '<one:custom_one><two:custom_two>';

        $this->assertEquals($expected, View::make('test')->render());
    }
}
