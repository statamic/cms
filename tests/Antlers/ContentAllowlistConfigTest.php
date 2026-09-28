<?php

namespace Tests\Antlers;

use Statamic\Contracts\View\Antlers\Parser as ParserContract;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;

class ContentAllowlistConfigTest extends ParserTestCase
{
    public function test_shipped_antlers_config_yields_default_allowlists()
    {
        app(ParserContract::class);

        $this->assertContains('trans:*', GlobalRuntimeState::$allowedContentTagPaths);
        $this->assertContains('lower', GlobalRuntimeState::$allowedContentModifierPaths);
    }

    public function test_null_allowed_content_config_uses_statamic_defaults()
    {
        config([
            'statamic.antlers.allowedContentTags' => null,
            'statamic.antlers.allowedContentModifiers' => null,
        ]);

        app(ParserContract::class);

        $tags = GlobalRuntimeState::$allowedContentTagPaths;
        $modifiers = GlobalRuntimeState::$allowedContentModifierPaths;

        $this->assertContains('trans:*', $tags);
        $this->assertGreaterThanOrEqual(4, count($tags));
        $this->assertContains('lower', $modifiers);
        $this->assertGreaterThanOrEqual(150, count($modifiers));
    }

    public function test_empty_array_allows_no_content_tags_or_modifiers()
    {
        config([
            'statamic.antlers.allowedContentTags' => [],
            'statamic.antlers.allowedContentModifiers' => [],
        ]);

        app(ParserContract::class);

        $this->assertSame([], GlobalRuntimeState::$allowedContentTagPaths);
        $this->assertSame([], GlobalRuntimeState::$allowedContentModifierPaths);
    }

    public function test_at_default_expands_core_tags_and_keeps_custom_patterns()
    {
        config(['statamic.antlers.allowedContentTags' => ['@default', 'custom_allowlist_tag:*']]);

        app(ParserContract::class);

        $allowed = GlobalRuntimeState::$allowedContentTagPaths;

        $this->assertContains('trans:*', $allowed);
        $this->assertContains('custom_allowlist_tag:*', $allowed);
    }

    public function test_at_default_expands_core_modifiers_and_keeps_custom_handles()
    {
        config(['statamic.antlers.allowedContentModifiers' => ['@default', 'custom_allowlist_modifier']]);

        app(ParserContract::class);

        $allowed = GlobalRuntimeState::$allowedContentModifierPaths;

        $this->assertContains('lower', $allowed);
        $this->assertContains('custom_allowlist_modifier', $allowed);
    }

    public function test_config_without_at_default_replaces_tag_allowlist()
    {
        config(['statamic.antlers.allowedContentTags' => ['only_custom:*']]);

        app(ParserContract::class);

        $allowed = GlobalRuntimeState::$allowedContentTagPaths;

        $this->assertSame(['only_custom:*'], $allowed);
        $this->assertNotContains('trans:*', $allowed);
    }

    public function test_config_without_at_default_replaces_modifier_allowlist()
    {
        config(['statamic.antlers.allowedContentModifiers' => ['only_custom_modifier']]);

        app(ParserContract::class);

        $allowed = GlobalRuntimeState::$allowedContentModifierPaths;

        $this->assertSame(['only_custom_modifier'], $allowed);
        $this->assertNotContains('lower', $allowed);
    }

    public function test_at_default_deduplicates_expanded_lists()
    {
        config(['statamic.antlers.allowedContentTags' => ['@default', '@default', 'dedupe_me:*']]);

        app(ParserContract::class);

        $allowed = GlobalRuntimeState::$allowedContentTagPaths;
        $counts = array_count_values($allowed);

        $this->assertSame(1, $counts['dedupe_me:*'] ?? 0);
        $this->assertSame(1, $counts['trans:*'] ?? 0);
    }
}
