<?php

namespace Tests\Antlers\Runtime;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Entries\Collection;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Text;
use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\Fixtures\Addon\Modifiers\VarTestModifier;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class ParameterStyleModifierTest extends ParserTestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function test_scope_is_not_impacted_by_parameter_style_modifiers()
    {
        Collection::make('blog')->routes('{slug}')->save();

        EntryFactory::collection('blog')->id('1')->data(['title' => '1-One'])->slug('one')->create();
        EntryFactory::collection('blog')->id('2')->data(['title' => '2-Two'])->slug('two')->create();
        EntryFactory::collection('blog')->id('3')->data(['title' => '3-Three'])->slug('three')->create();

        $data = [
            'hello' => 'Wilderness',
            'data' => [
                ['title' => 'One'],
                ['title' => 'Two'],
                ['title' => 'Three'],
            ],
        ];

        $template = <<<'EOT'
{{ collection:blog as="entries" }}

{{ collection from="blog" as="some_alias" }}
{{ entries scope="entry" }}
<{{ entry.title }}><{{ some_alias | length }}>
{{ /entries }}
{{ /collection }}

{{ /collection:blog }}
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', $template);

        $response = $this->get('one')->assertOk();

        $expected = <<<'EOT'
<1-One><3>

<2-Two><3>

<3-Three><3>
EOT;

        $this->assertSame($expected, StringUtilities::normalizeLineEndings(trim($response->content())));
    }

    public function test_modifier_style_parameters_applies_brace_escape_sequences()
    {
        VarTestModifier::register();
        $template = '{{ value var_test_modifier="test@{}|param_two@{@}" }} ';
        $this->renderString($template, ['value' => 'value'], true);

        $this->assertSame(['test{}', 'param_two{}'], VarTestModifier::$params);
    }

    public function test_parameter_style_modifiers_skip_null_like_chained_modifiers()
    {
        $data = ['empty_field' => new Value(null, 'empty_field', new Text)];

        $this->assertSame('', $this->renderString('{{ empty_field count="true" }}', $data, true));
        $this->assertSame('null', $this->renderString('{{ empty_field to_json="true" }}', $data, true));
    }

    public function test_parameter_style_modifiers_check_access_before_skipping_null()
    {
        $banned = GlobalRuntimeState::$bannedModifierPaths;
        GlobalRuntimeState::$bannedModifierPaths = ['upper'];
        GlobalRuntimeState::$throwErrorOnAccessViolation = true;

        $this->expectException(RuntimeException::class);

        try {
            $this->renderString('{{ empty_field upper="true" }}', ['empty_field' => new Value(null, 'empty_field', new Text)], true);
        } finally {
            GlobalRuntimeState::$bannedModifierPaths = $banned;
        }
    }
}
