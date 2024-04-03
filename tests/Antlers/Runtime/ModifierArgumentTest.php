<?php

namespace Tests\Antlers\Runtime;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Entries\EntryCollection;
use Tests\Antlers\Fixtures\Addon\Modifiers\VarTestModifier;
use Tests\Antlers\Fixtures\Addon\Tags\VarTestTags as VarTest;
use Tests\Antlers\ParserTestCase;
use Tests\PreventSavingStacheItemsToDisk;

class ModifierArgumentTest extends ParserTestCase
{
    use PreventSavingStacheItemsToDisk;

    public function test_environment_does_not_resolve_collections_being_sent_to_modifiers()
    {
        VarTestModifier::register();

        EntryFactory::collection('test')->id('one')->slug('one')->data(['title' => 'One'])->create();
        EntryFactory::collection('test')->id('two')->slug('two')->data(['title' => 'Two'])->create();

        VarTest::register();

        $template = <<<'EOT'
{{ collection:test as="entries" }}
    {{ var_test :variable="entries" }}
{{ /collection:test }}
EOT;

        $this->renderString($template, [], true);
        $this->assertInstanceOf(EntryCollection::class, VarTest::$var);

        $template = <<<'EOT'
{{ collection:test as="entries" }}
    {{ entries var_test_modifier="true" }}
{{ /collection:test }}
EOT;

        $this->renderString($template, []);
        $this->assertInstanceOf(EntryCollection::class, VarTestModifier::$value);
    }

    public function test_in_array_with_collections()
    {
        $data = [
            'items' => collect([
                'bat',
                'ball',
                'basket',
            ]),
        ];

        $template = <<<'EOT'
<{{ if items | contains('bat') }}Yes{{ /if }}><{{ if items | contains('zebra') }}Yes{{ else }}No{{ /if }}>
EOT;

        $this->assertSame('<Yes><No>', $this->renderString($template, $data, true));
    }
}
