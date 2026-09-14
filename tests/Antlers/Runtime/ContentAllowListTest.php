<?php

namespace Tests\Antlers\Runtime;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Text;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Tests\Antlers\ParserTestCase;

class ContentAllowListTest extends ParserTestCase
{
    public function tearDown(): void
    {
        GlobalRuntimeState::$allowedContentTagPaths = [];
        GlobalRuntimeState::$allowedContentModifierPaths = [];
        GlobalRuntimeState::$bannedContentTagPaths = [];
        GlobalRuntimeState::$bannedContentModifierPaths = [];
        GlobalRuntimeState::$bannedTagPaths = [];
        GlobalRuntimeState::$bannedModifierPaths = [];
        GlobalRuntimeState::$throwErrorOnAccessViolation = false;
        GlobalRuntimeState::$isEvaluatingUserData = false;

        parent::tearDown();
    }

    #[Test]
    public function allowed_modifier_can_be_used_in_user_content()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = ['upper'];

        $value = $this->makeAntlersTextValue('{{ title | upper }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'title' => 'hello',
        ], true, true);

        $this->assertSame('HELLO', $result);
    }

    #[Test]
    public function disallowed_modifier_is_blocked_in_user_content()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = ['upper'];

        $value = $this->makeAntlersTextValue('{{ title | lower }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'title' => 'HELLO',
        ], true, true);

        $this->assertSame('HELLO', $result);
    }

    #[Test]
    public function empty_modifier_allow_list_blocks_all_modifiers_in_user_content()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = [];

        $value = $this->makeAntlersTextValue('{{ title | upper }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'title' => 'hello',
        ], true, true);

        $this->assertSame('hello', $result);
    }

    #[Test]
    public function modifier_block_list_overrides_modifier_allow_list_in_user_content()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = ['upper'];
        GlobalRuntimeState::$bannedContentModifierPaths = ['upper'];

        $value = $this->makeAntlersTextValue('{{ title | upper }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'title' => 'hello',
        ], true, true);

        $this->assertSame('hello', $result);
    }

    #[Test]
    public function disallowed_modifier_throws_when_access_violations_are_enabled()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = ['upper'];
        GlobalRuntimeState::$throwErrorOnAccessViolation = true;

        $this->expectException(RuntimeException::class);

        $value = $this->makeAntlersTextValue('{{ title | lower }}');
        $this->renderString('{{ text_field }}', [
            'text_field' => $value,
            'title' => 'HELLO',
        ], true, true);
    }

    #[Test]
    public function allow_list_does_not_affect_modifier_usage_in_trusted_templates()
    {
        GlobalRuntimeState::$allowedContentModifierPaths = [];

        $result = $this->renderString('{{ title | lower }}', [
            'title' => 'HELLO',
        ], true, true);

        $this->assertSame('hello', $result);
    }

    #[Test]
    public function allowed_tag_pattern_can_be_used_in_user_content()
    {
        $this->registerRuntimeTestTag();
        GlobalRuntimeState::$allowedContentTagPaths = ['runtime_test_tag:*'];

        $value = $this->makeAntlersTextValue('{{ runtime_test_tag }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
        ], true, true);

        $this->assertSame('tag-ok', $result);
    }

    #[Test]
    public function disallowed_tag_pattern_is_blocked_in_user_content()
    {
        $this->registerRuntimeTestTag();
        GlobalRuntimeState::$allowedContentTagPaths = ['other_tag'];

        $value = $this->makeAntlersTextValue('{{ runtime_test_tag }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
        ], true, true);

        $this->assertSame('', $result);
    }

    #[Test]
    public function empty_tag_allow_list_blocks_all_tags_in_user_content()
    {
        $this->registerRuntimeTestTag();
        GlobalRuntimeState::$allowedContentTagPaths = [];

        $value = $this->makeAntlersTextValue('{{ runtime_test_tag }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
        ], true, true);

        $this->assertSame('', $result);
    }

    #[Test]
    public function tag_block_list_overrides_tag_allow_list_in_user_content()
    {
        $this->registerRuntimeTestTag();
        GlobalRuntimeState::$allowedContentTagPaths = ['runtime_test_tag:*'];
        GlobalRuntimeState::$bannedContentTagPaths = ['runtime_test_tag:*'];

        $value = $this->makeAntlersTextValue('{{ runtime_test_tag }}');
        $result = $this->renderString('{{ text_field }}', [
            'text_field' => $value,
        ], true, true);

        $this->assertSame('', $result);
    }

    #[Test]
    public function allow_list_does_not_affect_tag_usage_in_trusted_templates()
    {
        $this->registerRuntimeTestTag();
        GlobalRuntimeState::$allowedContentTagPaths = [];
        GlobalRuntimeState::$bannedTagPaths = ['another_tag'];

        $result = $this->renderString('{{ runtime_test_tag }}', [], true, true);
        $this->assertSame('tag-ok', $result);
    }

    #[Test]
    public function app_tags_from_app_directory_are_included_in_default_allowed_content_tags()
    {
        \App\Tags\AppTestTag::register();

        $result = (string) Antlers::parse('{{ app_test_tag }}', [], false);

        $this->assertSame('app-tag-ok', $result, 'Tags in App\Tags should be auto-allowed in user content when using default config.');
    }

    #[Test]
    public function app_modifiers_from_app_directory_are_included_in_default_allowed_content_modifiers()
    {
        \App\Modifiers\AppTestModifier::register();

        $result = (string) Antlers::parse('{{ value | app_test_modifier }}', ['value' => 'hello'], false);

        $this->assertSame('HELLO-app-modifier', $result, 'Modifiers in App\Modifiers should be auto-allowed in user content when using default config.');
    }

    private function makeAntlersTextValue(string $template): Value
    {
        $textFieldtype = new Text();
        $field = new Field('text_field', [
            'type' => 'text',
            'antlers' => true,
        ]);

        $textFieldtype->setField($field);

        return new Value($template, 'text_field', $textFieldtype);
    }

    private function registerRuntimeTestTag(): void
    {
        (new class extends Tags
        {
            public static $handle = 'runtime_test_tag';

            public function index()
            {
                return 'tag-ok';
            }
        })::register();
    }
}
