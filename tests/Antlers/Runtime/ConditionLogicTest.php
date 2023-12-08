<?php

namespace Tests\Antlers\Runtime;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Entries\Collection;
use Statamic\Fields\Field;
use Statamic\Fields\LabeledValue;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Code;
use Statamic\Fieldtypes\Select;
use Statamic\Support\Arr;
use Statamic\Tags\Tags;
use Statamic\Taxonomies\TermCollection;
use Statamic\View\Antlers\Language\Exceptions\AntlersException;
use Statamic\View\Cascade;
use Tests\Antlers\Fixtures\Addon\Tags\VarTest;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class ConditionLogicTest extends ParserTestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function test_negation_following_or_is_evaluated()
    {
        $template = '{{ if !first && first_row_headers || !first_row_headers }}yes{{ else }}no{{ /if }}';

        $this->assertSame('yes', $this->renderString($template, [
            'first_row_headers' => false,
        ]));
        $this->assertSame('no', $this->renderString($template, [
            'first_row_headers' => true,
            'first' => true,
        ]));
        $this->assertSame('yes', $this->renderString($template, [
            'first_row_headers' => false,
            'first' => true,
        ]));
        $this->assertSame('yes', $this->renderString($template, [
            'first_row_headers' => false,
            'first' => false,
        ]));
    }

    public function test_negation_equivalency()
    {
        $data = ['variable' => false];

        $this->assertFalse($this->evaluateRaw('!!variable', $data));
        $this->assertFalse($this->evaluateRaw('!!(variable)', $data));
        $this->assertFalse($this->evaluateRaw('!(!variable)', $data));

        $this->assertTrue($this->evaluateRaw('!variable', $data));
        $this->assertTrue($this->evaluateRaw('(!variable)', $data));
        $this->assertTrue($this->evaluateRaw('!(variable)', $data));
        $this->assertTrue($this->evaluateRaw('!!!variable', $data));

        $this->assertTrue($this->evaluateRaw('!variable && !variable', $data));
        $this->assertTrue($this->evaluateRaw('!variable && !!(!variable)', $data));
    }

    public function test_comparison_operators()
    {
        $this->assertTrue($this->evaluateRaw('10 > 5'));
        $this->assertTrue($this->evaluateRaw('10 > 5 && 5 > 2'));
        $this->assertFalse($this->evaluateRaw('10 > 5 && 5 < 2'));

        $this->assertFalse($this->evaluateRaw('5 < 5'));
        $this->assertTrue($this->evaluateRaw('5 <= 5'));

        $this->assertFalse($this->evaluateRaw('5 > 5'));
        $this->assertTrue($this->evaluateRaw('5 >= 5'));

        $this->assertFalse($this->evaluateRaw('3 + 2 > 5'));
        $this->assertTrue($this->evaluateRaw('3 + 2 >= 5'));

        $this->assertTrue($this->evaluateRaw('5 == "5"'));
        $this->assertFalse($this->evaluateRaw('5 === "5"'));

        $this->assertTrue($this->evaluateRaw('5 != 4'));
        $this->assertFalse($this->evaluateRaw('5 !== 5'));
        $this->assertTrue($this->evaluateRaw('5 !== "5"'));

        $this->assertTrue($this->evaluateRaw('5 < 10'));
        $this->assertTrue($this->evaluateRaw('5 <= 10'));
        $this->assertFalse($this->evaluateRaw('5 < 3'));
        $this->assertFalse($this->evaluateRaw('5 <= 3'));
    }

    public function test_modifiers_in_multiple_conditions()
    {
        $data = ['var' => [1, 2, 3]];

        $template = <<<'EOT'
{{ if (var | count) > 1 && (var | count) < 4 }}yes{{ else }}no{{ /if }}
EOT;

        $this->assertSame('yes', $this->renderString($template, $data));
        $this->assertSame('no', $this->renderString($template, [
            'var' => [1, 2, 3, 4, 5, 6, 7],
        ]));
    }

    public function test_shorthand_logical_and_equivalency()
    {
        $this->assertSame('yes', $this->renderString('{{ if true & true }}yes{{ else }}no{{ /if }}'));
        $this->assertSame('yes', $this->renderString('{{ if true && true }}yes{{ else }}no{{ /if }}'));
    }

    public function test_switch_operator()
    {
        $template = <<<'EOT'
{{ switch (
    (value == 1) => {'First'},
    (value == 13) => {'Second'},
    () => {"Default"}
 ) }}
EOT;

        $this->assertSame('First', $this->renderString($template, ['value' => 1]));
        $this->assertSame('Second', $this->renderString($template, ['value' => 13]));
        $this->assertSame('Default', $this->renderString($template, ['value' => 2]));
        $this->assertSame('Default', $this->renderString($template, ['value' => 23]));
    }

    public function test_switch_invalid_default_position_throws_exception()
    {
        $this->expectException(AntlersException::class);
        $template = <<<'EOT'
{{ switch (
    () => {"Default"},
    (value == 1) => {'First'},
    (value == 13) => {'Second'},
 ) }}
EOT;

        $this->renderString($template, []);
    }

    public function test_missing_arg_separator_after_first_case_statement_throws_exception()
    {
        $this->expectException(AntlersException::class);
        $template = <<<'EOT'
{{ switch (
    () => {"Default"}
    (value == 1) => {'First'},
    (value == 13) => {'Second'},
 ) }}
EOT;

        $this->renderString($template, []);
    }

    public function test_missing_arg_separator_after_other_case_statement_throws_exception()
    {
        $this->expectException(AntlersException::class);
        $template = <<<'EOT'
{{ switch (
    () => {"Default"},
    (value == 1) => {'First'},
    (value == 31) => {'Another'}
    (value == 13) => {'Second'},
 ) }}
EOT;

        $this->renderString($template, []);
    }

    public function test_switch_operator_is_assignable()
    {
        $template = <<<'EOT'
{{ my_var = switch ((value == 1) => {'First'}, (value == 13) => {'Second'}, () => {"Default"} ) }}Result: {{ my_var }}
EOT;

        $this->assertSame('Result: First', $this->renderString($template, ['value' => 1]));
        $this->assertSame('Result: Second', $this->renderString($template, ['value' => 13]));
        $this->assertSame('Result: Default', $this->renderString($template, ['value' => 2]));
        $this->assertSame('Result: Default', $this->renderString($template, ['value' => 23]));
    }

    public function test_switch_operator_can_use_multiple_scope_variables()
    {
        $template = <<<'EOT'
{{ my_var = switch (
       (value == 1 && value_two == 2) => {'First'},
       (value == 13 && value_two == 1) => {'Second'},
       () => {"Default"}
  ) }}Result: {{ my_var }}
EOT;

        $this->assertSame('Result: Second', $this->renderString($template, [
            'value' => 13,
            'value_two' => 1,
        ]));
        $this->assertSame('Result: Default', $this->renderString($template, [
            'value' => 1,
            'value_two' => 1,
        ]));
        $this->assertSame('Result: First', $this->renderString($template, [
            'value' => 1,
            'value_two' => 2,
        ]));

        $template = <<<'EOT'
{{ my_var = switch (
       (value == 1 && value_two == 2) => 'First',
       (value == 13 && value_two == 1) => 'Second',
       () => "Default"
  )
}}Result: {{ my_var }}
EOT;

        $this->assertSame('Result: Second', $this->renderString($template, [
            'value' => 13,
            'value_two' => 1,
        ]));
        $this->assertSame('Result: Default', $this->renderString($template, [
            'value' => 1,
            'value_two' => 1,
        ]));
        $this->assertSame('Result: First', $this->renderString($template, [
            'value' => 1,
            'value_two' => 2,
        ]));
    }

    public function test_switch_operator_is_assignable_from_interpolated_expression()
    {
        $template = <<<'EOT'
{{ my_var = {switch ((value == 1) => {'First'}, (value == 13) => {'Second'}, () => {"Default"} )} }}Result: {{ my_var }}
EOT;

        $this->assertSame('Result: First', $this->renderString($template, ['value' => 1]));
        $this->assertSame('Result: Second', $this->renderString($template, ['value' => 13]));
        $this->assertSame('Result: Default', $this->renderString($template, ['value' => 2]));
        $this->assertSame('Result: Default', $this->renderString($template, ['value' => 23]));
    }

    public function test_switch_operator_with_simplified_conditions()
    {
        $template = <<<'EOT'
{{
    switch (
        (['doc', 'docx'] | in_array(filetype)) => 'word.svg',
        () => 'default.svg'
    )
}}
EOT;

        $this->assertSame('word.svg', $this->renderString($template, ['filetype' => 'doc']));
    }

    public function test_deferred_expressions_are_evaluated_correctly_from_ternary_conditions_relaxed()
    {
        (new class extends Tags
        {
            public static $handle = 'tag-pages';

            public function index()
            {
                return [
                    ['title' => 'Page 1'],
                    ['title' => 'Page 2'],
                    ['title' => 'Page 3'],
                ];
            }
        })::register();

        (new class extends Tags
        {
            public static $handle = 'tag-articles';

            public function index()
            {
                return [
                    ['title' => 'Article 1'],
                    ['title' => 'Article 2'],
                    ['title' => 'Article 3'],
                ];
            }
        })::register();

        $template = <<<'EOT'
{{ my_results = {
    use_pages ? {
        tag-pages
    } : {
        tag-articles
    }
} }}{{ title }}{{ /my_results }}
EOT;

        $this->assertSame('Page 1Page 2Page 3', $this->renderString($template, ['use_pages' => true], true));
        $this->assertSame('Article 1Article 2Article 3', $this->renderString($template, ['use_pages' => false], true));
    }

    public function test_deferred_expressions_are_evaluated_correctly_from_ternary_conditions_tight()
    {
        (new class extends Tags
        {
            public static $handle = 'tag-pages';

            public function index()
            {
                return [
                    ['title' => 'Page 1'],
                    ['title' => 'Page 2'],
                    ['title' => 'Page 3'],
                ];
            }
        })::register();

        (new class extends Tags
        {
            public static $handle = 'tag-articles';

            public function index()
            {
                return [
                    ['title' => 'Article 1'],
                    ['title' => 'Article 2'],
                    ['title' => 'Article 3'],
                ];
            }
        })::register();

        $template = <<<'EOT'
{{ my_results = {use_pages ? {tag-pages} : {tag-articles}}}}{{ title }}{{ /my_results }}
EOT;

        $this->assertSame('Page 1Page 2Page 3', $this->renderString($template, ['use_pages' => true], true));
        $this->assertSame('Article 1Article 2Article 3', $this->renderString($template, ['use_pages' => false], true));
    }

    public function test_modifier_chains_break_on_equality_comparison_operator()
    {
        $template = <<<'EOT'
{{ if global:notification && global:notification|strip_tags|is_empty == false }}Not Empty{{ else}}Empty{{ /if }}
EOT;

        $data = [
            'global' => [
                'notification' => '<p></p>',
            ],
        ];

        $this->assertSame('Empty', $this->renderString($template, $data, true));

        $data = [
            'global' => [
                'notification' => '<p>Hello, world.</p>',
            ],
        ];

        $this->assertSame('Not Empty', $this->renderString($template, $data, true));
    }

    public function test_empty_terms_collection_is_falsey()
    {
        $terms = new TermCollection();
        $value = new Value($terms);

        $template = '{{ if topics }}yes{{ else }}no{{ /if }}';
        $this->assertSame('no', $this->renderString($template, [
            'topics' => $value,
        ]));
    }

    public function test_values_are_resolved_in_conditions()
    {
        $fieldType = new Select();

        // Values are different from handle here to ensure that it returns the value
        // and not the name of the variable, and to ensure it's not the handle.
        $visual = new Value('visual-value', 'visual', $fieldType);
        $semantic = new Value('semantic-value', 'semantic', $fieldType);

        $data = [
            'semantic' => $semantic,
            'visual' => $visual,
        ];

        VarTest::register();

        $template = <<<'EOT'
{{ var_test variable="{ visual == 'visual-value' ? visual : semantic }" }}
EOT;

        $this->renderString($template, $data, true);
        $this->assertSame('visual-value', VarTest::$var);

        $template = <<<'EOT'
{{ var_test variable="{ visual == 'not_visual' ? visual : semantic }" }}
EOT;
        $this->renderString($template, $data, true);
        $this->assertSame('semantic-value', VarTest::$var);

        // This, by contrast, should respect the value objects.
        $template = <<<'EOT'
{{ var_test :variable="visual == 'not_visual' ? visual : semantic" }}
EOT;
        $this->renderString($template, $data, true);
        $this->assertInstanceOf(LabeledValue::class, VarTest::$var);
        $this->assertSame('semantic-value', (string) VarTest::$var);

        $template = <<<'EOT'
{{ var_test :variable="visual == 'visual-value' ? visual : semantic" }}
EOT;
        $this->renderString($template, $data, true);
        $this->assertInstanceOf(LabeledValue::class, VarTest::$var);
        $this->assertSame('visual-value', (string) VarTest::$var);
    }

    public function test_conditional_with_not_keyword()
    {
        $template = <<<'EOT'
{{ if not seo_noindex }}
    {{ if seo_canonical_type == 'current' }}
        <link rel="canonical" href="{{ config:app:url }}{{ seo_canonical_current | url }}">
    {{ elseif seo_canonical_type == 'external' }}
        <link rel="canonical" href="{{ seo_canonical_external }}">
    {{ elseif seo_canonical_type == 'entry' }}
        <link rel="canonical" href="{{ permalink }}">
    {{ /if }}
{{ else }}
    Inside else: not seo_noindex
{{ /if }}
EOT;

        $this->assertStringContainsString('Inside else: not seo_noindex', $this->renderString($template, ['seo_noindex' => true]));
        $this->assertStringContainsString('<link rel="canonical" href="The permalink">', $this->renderString($template, [
            'seo_noindex' => false,
            'seo_canonical_type' => 'entry',
            'permalink' => 'The permalink',
        ]));
    }

    public function test_templates_with_many_unique_conditions_render_correctly()
    {
        $template = <<<'EOT'


<!-- /snippets/_seo.antlers.html -->
<title>
    {{ yield:seo_title }}
    {{ seo_title ? seo_title : title }}
    {{ seo:title_separator ? seo:title_separator : " &#124; " }}
    {{ seo:change_page_title where="collection:{collection}" }}
        {{ if what_to_add == 'collection_title' }}
            {{ collection:title }}
        {{ elseif what_to_add == 'custom_text' }}
            {{ custom_text }}
        {{ /if }}
        {{ seo:title_separator ? seo:title_separator : " &#124; " }}
    {{ /seo:change_page_title }}
    {{ seo:site_name ? seo:site_name : config:app:name }}
</title>

{{ if seo_description }}
    <meta name="description" content="{{ seo_description }}">
{{ elseif seo:collection_defaults }}
    <meta name="description" content="{{ partial:snippets/fallback_description }}">
{{ /if }}

{{ if
    (environment == 'local' && !seo:noindex_local) or
    (environment == 'staging' && !seo:noindex_staging) or
    (environment == 'production' && !seo:noindex_production)
}}
    {{ if seo_noindex & seo_nofollow }}
        <meta name="robots" content="noindex, nofollow">
    {{ elseif seo_nofollow }}
        <meta name="robots" content="nofollow">
    {{ elseif seo_noindex }}
        <meta name="robots" content="noindex">
    {{ /if }}
{{ else }}
    <meta name="robots" content="noindex, nofollow">
{{ /if }}

{{ if seo:hreflang_auto }}
    {{ if not seo_noindex and seo_canonical_type == 'entry' and current_full_url === permalink }}
        {{ locales all="false" }}
            <link rel="alternate" hreflang="{{ locale:full | replace('_','-') }}" href="{{ permalink }}">
        {{ /locales }}
    {{ /if }}
{{ /if }}

{{ if not seo_noindex }}
    {{ if seo_canonical_type == 'current' }}
        <link rel="canonical" href="{{ config:app:url }}{{ seo_canonical_current | url }}">
    {{ elseif seo_canonical_type == 'external' }}
        <link rel="canonical" href="{{ seo_canonical_external }}">
    {{ elseif seo_canonical_type == 'entry' }}
        <link rel="canonical" href="{{ permalink }}">
    {{ /if }}
{{ else }}
    Inside else: not seo_noindex
{{ /if }}

{{ yield:pagination }}

{{ if seo:json_ld_type && seo:json_ld_type != 'none' }}
    <script type="application/ld+json" id="schema">
        {{ if seo:json_ld_type == 'organization'  }}
            {
                "@context": "http://schema.org",
                "@type": "Organization",
                "name": "{{ seo:organization_name }}",
                "url": "{{ config:app:url }}{{ homepage }}"{{ if seo:organization_logo }},
                "logo": "{{ config:app:url }}{{ glide:seo:organization_logo width='336' height='336' fit='contain' }}"{{ /if }}
            }
        {{ elseif seo:json_ld_type == 'person' }}
            {
                "@context": "http://schema.org",
                "@type": "Person",
                "url": "{{ config:app:url }}{{ homepage }}",
                "name": "{{ seo:person_name }}"
            }
        {{ elseif seo:json_ld_type == 'custom' }}
            {{ seo:json_ld }}
        {{ /if }}
    </script>
{{ /if }}

{{ if schema_jsonld  }}
    <script type="application/ld+json">{{ schema_jsonld }}</script>
{{ /if }}

{{ if seo:breadcrumbs && segment_1 }}
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [
                {{ nav:breadcrumbs }}
                    {
                        "@type": "ListItem",
                        "position": {{ count }},
                        "name": "{{ title }}",
                        "item": "{{ permalink }}"
                    } {{ unless last}},{{ /unless}}
                {{ /nav:breadcrumbs }}
            ]
        }
    </script>
{{ /if }}

<meta property="og:site_name" content="{{ seo:site_name ? seo:site_name : config:app:name }}">
<meta property="og:type" content="website">
<meta property="og:locale" content="{{ site:locale }}">
{{ if og_title }}
    <meta property="og:title" content="{{ og_title }}">
{{ else }}
    <meta property="og:title" content="{{ seo_title ? seo_title : title }}">
{{ /if }}
{{ if og_description }}
    <meta property="og:description" content="{{ og_description }}">
{{ elseif seo_description }}
    <meta property="og:description" content="{{ seo_description }}">
{{ elseif seo:collection_defaults }}
    <meta property="og:description" content="{{ partial:snippets/fallback_description }}">
{{ /if }}
{{ if og_image }}
    <meta property="og:image" content="{{ glide:og_image width='1200' height='630' fit='crop_focal' absolute="true" }}">
{{ elseif seo:og_image }}
    <meta property="og:image" content="{{ glide:seo:og_image width='1200' height='630' fit='crop_focal' absolute="true" }}">
{{ /if }}

{{ if twitter_image or seo:twitter_image }}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="{{ seo:twitter_handle }}">
    {{ if og_title }}
        <meta name="twitter:title" content="{{ og_title }}">
    {{ else }}
        <meta name="twitter:title" content="{{ seo_title ? seo_title : title }}">
    {{ /if }}
    {{ if og_description }}
        <meta name="twitter:description" content="{{ og_description }}">
    {{ elseif seo_description }}
        <meta name="twitter:description" content="{{ seo_description }}">
    {{ elseif seo:collection_defaults }}
        <meta name="twitter:description" content="{{ partial:snippets/fallback_description }}">
    {{ /if }}
    {{ if twitter_image }}
        <meta name="twitter:image" content="{{ glide:twitter_image width='1200' height='600' fit='crop_focal' absolute="true" }}">
        {{ asset :url="twitter_image" }}
            {{ if alt }}
                <meta name="twitter:image:alt" content="{{ alt ensure_right='.' }}">
            {{ /if }}
        {{ /asset }}
    {{ elseif seo:twitter_image }}
        <meta name="twitter:image" content="{{ glide:seo:twitter_image width='1200' height='600' fit='crop_focal' absolute="true" }}">
        {{ asset :url="seo:twitter_image" }}
            {{ if alt }}
                <meta name="twitter:image:alt" content="{{ alt ensure_right='.' }}">
            {{ /if }}
        {{ /asset }}
    {{ /if }}
{{ /if }}

{{ if
    (environment == 'local' && seo:trackers_local) or
    (environment == 'staging' && seo:trackers_staging) or
    (environment == 'production' && seo:trackers_production)
}}
    {{ if seo:tracker_type == 'gtm' }}
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ seo:google_tag_manager }}');function gtag(){dataLayer.push(arguments);}</script>
    {{ elseif seo:tracker_type == 'gtag' }}
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ seo:google_analytics }}"></script>
        <script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('set', new Date());gtag('config', '{{ seo:google_analytics }}' {{ if seo:anonymize_ip }}, {'anonymize_ip': true}{{ /if }});</script>
    {{ /if }}
    {{ if seo:use_cookie_banner }}
        <script>
            gtag('consent', 'default', {
                'ad_storage': 'denied',
                'analytics_storage': 'denied',
                'wait_for_update': 500
            });
            dataLayer.push({
                'event': 'default_consent'
            });
        </script>
    {{ /if }}

    {{ section:seo_body }}
        {{ if seo:tracker_type == 'gtm' }}
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ seo:google_tag_manager }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        {{ /if }}
        {{ if seo:use_cookie_banner }}
            {{ partial:components/cookie_banner }}
        {{ /if }}
    {{ /section:seo_body }}

    {{ if seo:use_google_site_verification }}
        <meta name="google-site-verification" content="{{ seo:google_site_verification }}" />
    {{ /if }}

    {{ if seo:use_fathom && seo:fathom_use_custom_domain }}
        <script src="{{ seo:fathom_custom_script_url }}" site="{{ seo:fathom }}" defer></script>
    {{ elseif seo:use_fathom }}
        <script src="https://cdn.usefathom.com/script.js" site="{{ seo:fathom }}" defer></script>
    {{ /if }}

    {{ if seo:use_cloudflare_web_analytics }}
        <script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "{{ seo:cloudflare_web_analytics }}"}'></script>
    {{ /if }}
{{ /if }}
EOT;

        $data = [
            'environment' => 'local',
            'title' => 'The Title',
            'seo' => [
                'trackers_local' => true,
                'use_fathom' => true,
                'fathom_use_custom_domain' => true,
                'fathom_custom_script_url' => 'test url',
                'fathom' => 'site name',
                'use_cloudflare_web_analytics' => true,
            ],
            'seo_noindex' => true,
        ];

        $result = $this->renderString($template, $data);

        // If this fails, the pairing algorithm incorrectly associated something along the way.
        $this->assertStringContainsString('The Title', $result);
        $this->assertStringContainsString('test url', $result);
        $this->assertStringContainsString('site name', $result);
        $this->assertStringContainsString('https://static.cloudflareinsights.com/beacon.min.js', $result);
        $this->assertStringContainsString('Inside else: not seo_noindex', $result);

        Arr::set($data, 'seo.fathom_use_custom_domain', false);
        Arr::set($data, 'seo_title', 'The Seo Title');
        Arr::set($data, 'seo_description', 'The SEO Description');

        $result = $this->renderString($template, $data);
        $this->assertStringContainsString('The Seo Title', $result);
        $this->assertStringContainsString('<meta name="description" content="The SEO Description">', $result);
        $this->assertStringContainsString('https://cdn.usefathom.com/script.js', $result);
        $this->assertStringContainsString('https://static.cloudflareinsights.com/beacon.min.js', $result);
        $this->assertStringContainsString('Inside else: not seo_noindex', $result);

        Arr::set($data, 'seo_noindex', false);
        Arr::set($data, 'seo_canonical_type', 'entry');
        Arr::set($data, 'permalink', 'The permalink');

        $result = $this->renderString($template, $data);
        $this->assertStringContainsString('The Seo Title', $result);
        $this->assertStringContainsString('<meta name="description" content="The SEO Description">', $result);
        $this->assertStringContainsString('https://cdn.usefathom.com/script.js', $result);
        $this->assertStringContainsString('https://static.cloudflareinsights.com/beacon.min.js', $result);
        $this->assertStringContainsString('<link rel="canonical" href="The permalink">', $result);

        Arr::set($data, 'schema_jsonld', 'json_ld');

        $result = $this->renderString($template, $data);
        $this->assertStringContainsString('The Seo Title', $result);
        $this->assertStringContainsString('<meta name="description" content="The SEO Description">', $result);
        $this->assertStringContainsString('https://cdn.usefathom.com/script.js', $result);
        $this->assertStringContainsString('https://static.cloudflareinsights.com/beacon.min.js', $result);
        $this->assertStringContainsString('<link rel="canonical" href="The permalink">', $result);
        $this->assertStringContainsString('<script type="application/ld+json">json_ld</script>', $result);

        Arr::set($data, 'twitter_image', true);
        Arr::set($data, 'seo.twitter_handle', 'The Twitter handle');

        $result = $this->renderString($template, $data);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $result);
        $this->assertStringContainsString('<meta name="twitter:site" content="The Twitter handle">', $result);
        $this->assertStringContainsString('The Seo Title', $result);
        $this->assertStringContainsString('<meta name="description" content="The SEO Description">', $result);
        $this->assertStringContainsString('https://cdn.usefathom.com/script.js', $result);
        $this->assertStringContainsString('https://static.cloudflareinsights.com/beacon.min.js', $result);
        $this->assertStringContainsString('<link rel="canonical" href="The permalink">', $result);
        $this->assertStringContainsString('<script type="application/ld+json">json_ld</script>', $result);
    }

    public function test_conditions_lock_processor_scope_before_processing()
    {
        EntryFactory::collection('blog')->id('1')->data(['title' => '1-One'])->create();
        EntryFactory::collection('blog')->id('2')->data(['title' => '2-Two'])->create();
        EntryFactory::collection('blog')->id('3')->data(['title' => '3-Three'])->create();

        $data = [
            'data' => 'root value',
        ];

        $template = <<<'EOT'
{{ collection:blog limit="2" sort="title|asc" as="entries" }}
{{ if entries.0.title == '1-One' }}
<{{ entries.0.title }}><{{ data }}><{{ entries.1.title }}><{{ data }}><{{ entries.0.title }}><{{ data }}>
{{ /if }}

{{ if entries.0.title == 'Two' }}
<{{ entries.0.title }}><{{ data }}>
<{{ entries.1.title }}><{{ data }}>
<{{ entries.0.title }}><{{ data }}>
{{ else }}
<else><{{ entries.0.title }}><{{ data }}><else><{{ entries.1.title }}><{{ data }}><else><{{ entries.0.title }}><{{ data }}>
{{ /if }}
{{ /collection:blog }}
EOT;

        $result = $this->renderString($template, $data, true);

        $this->assertStringContainsString('<1-One><root value><2-Two><root value><1-One><root value>', $result);
        $this->assertStringContainsString('<else><1-One><root value><else><2-Two><root value><else><1-One><root value>', $result);
    }

    public function test_conditions_reach_into_the_cascade()
    {
        $cascade = $this->mock(Cascade::class, function ($m) {
            $value = new LabeledValue('entry', 'Privacy Statement Type');

            $m->shouldReceive('get')->with('configuration')->andReturn([
                'privacy_statement_type' => $value,
            ]);
        });

        $template = <<<'EOT'
{{ if configuration:privacy_statement_type == 'entry' }}Yes{{ else }}No{{ /if }}
EOT;

        $this->assertSame('Yes', (string) $this->parser()->cascade($cascade)->parse($template));
    }

    public function test_uppercase_logical_keywords_in_conditions()
    {
        $template = <<<'EOT'
{{ if true AND false }}Yes{{ else }}No{{ /if }}
EOT;

        $this->assertSame('No', $this->renderString($template));
    }

    public function test_arrayable_strings_inside_conditions_used_with_modifiers()
    {
        $code = new Code();
        $field = new Field('code_field', [
            'type' => 'code',
            'antlers' => false,
        ]);

        $code->setField($field);
        $value = new Value('Oh hai, mark.', 'code_field', $code);

        $template = <<<'EOT'
{{ partial:test :code="code_field" }}
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('test', <<<'EOT'
{{ if code | starts_with('<div>') }}
Yes{{ else }}
No{{ /if }}
EOT

        );

        $this->assertSame('No', trim($this->renderString($template, ['code_field' => $value], true)));

        $this->viewShouldReturnRaw('test', <<<'EOT'
{{ if code | starts_with('Oh hai, mark.') }}
Yes{{ else }}
No{{ /if }}
EOT

        );

        $this->assertSame('Yes', trim($this->renderString($template, ['code_field' => $value], true)));
    }

    public function test_conditions_with_objects_inside_interpolations_dont_trigger_string_errors()
    {
        $collection = Collection::make('blog')->routes('{slug}')->save();

        $template = <<<'EOT'
{{ if items | where('collection', {collection}) }}
Yes
{{ /if }}
EOT;

        $data = [
            'items' => [
                [
                    'collection' => 'blog',
                ],
            ],
            'collection' => $collection,
        ];

        $this->assertSame('Yes', trim($this->renderString($template, $data, true)));
    }
}
