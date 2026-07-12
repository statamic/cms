<?php

namespace Tests\Antlers\Parser;

use Statamic\View\Antlers\Language\Parser\DocumentTransformer;
use Tests\Antlers\ParserTestCase;

class DocumentTransformerTest extends ParserTestCase
{
    public function test_transformer_does_not_skip_things()
    {
        $template = <<<'EOT'
{{ asset }}
{{ cache }}
{{ collection }}<random></random>
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
{{# Bard - bard #}}
{{ bard }}

    
    {{ if type == 'new_set' }}
    
        {{# Array Dynamic - array #}}
        {{ foreach:array_dynamic }}
        
            {{ key }} - {{ value }}
        {{ /foreach:array_dynamic }}
        {{# Array Keyed - array #}}
        {{ array_keyed }}
        
            {{# Label1 #}}
            {{ key1 }}
            {{# Label2 #}}
            {{ key2 }}
            
        {{ /array_keyed }}
        {{# Button Group - button_group #}}
        {{ button_group }}
        {{# Checkboxes - checkboxes #}}
        {{ checkboxes }}
        {{# Code - code #}}
        {{ code }}
        {{# Color - color #}}
        {{ color }}
        {{# Float - float #}}
        {{ float }}
        {{# Grid - grid #}}
        {{ grid }}
        
            {{# Grid Text 1 - text #}}
            {{ grid_text_1 }}
            {{# Grid Text 2 - text #}}
            {{ grid_text_2 }}
            
        {{ /grid }}
        {{# Stacked Grid - grid #}}
        {{ stacked_grid }}
        
            {{# Stacked Grid Text 1 - text #}}
            {{ stacked_grid_text_1 }}
            {{# Stacked Grid Text 2 - text #}}
            {{ stacked_grid_text_2 }}
            
        {{ /stacked_grid }}
        {{# Hidden Field - hidden #}}
        {{ hidden_field }}
        {{# HTML Field - html #}}
        {{ html_field }}
        {{# Integer Field - integer #}}
        {{ integer_field }}
        {{# Link Field - link #}}
        {{ link_field }}
        {{# Markdown - markdown #}}
        {{ markdown }}
        {{# Radio - radio #}}
        {{ radio }}
        {{# Range Field - range #}}
        {{ range_field }}
        {{# Select Field - select #}}
        {{ select_field }}
        {{# Table - table #}}
        {{ table | table }}
        {{# Textarea - textarea #}}
        {{ textarea }}
        {{# Time - time #}}
        {{ time }}
        {{# Toggle - toggle #}}
        {{ if toggle }} Yes. {{ /if }}
        {{# Video - video #}}
        {{ video }}
        {{# Yaml - yaml #}}
        {{ yaml:stacked_grid }}
            {{ stacked_grid_text_1 }}
            {{ stacked_grid_text_2 }}
        {{ /yaml:stacked_grid }}
        
        {{ yaml }}
            {{ grid }}
                {{ grid_text_1 }}
                {{ grid_text_2 }}
            {{ /grid }}
        {{ /yaml }}
        
        
        {{ yaml:grid }}
            {{ grid_text_1 }}
            {{ grid_text_2 }}
        {{ /yaml:grid }}
        
    {{ /if }}
{{ /bard }}

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
        
                {{# Bard - bard #}}
{{ bard }}

    
    {{ if type == 'new_set' }}
    
        {{# Array Dynamic - array #}}
        {{ foreach:array_dynamic }}
        
            {{ key }} - {{ value }}
        {{ /foreach:array_dynamic }}
        {{# Array Keyed - array #}}
                        {{ array_keyed }}
                        
                            {{# Label1 #}}
                            {{ key1 }}
                            {{# Label2 #}}
                            {{ key2 }}
                            
                        {{ /array_keyed }}
                        {{# Button Group - button_group #}}
                        {{ button_group }}
                        {{# Checkboxes - checkboxes #}}
                        {{ checkboxes }}
        {{# Code - code #}}
        {{ code }}
        {{# Color - color #}}
        {{ color }}
        {{# Float - float #}}
        {{ float }}
        {{# Grid - grid #}}
        {{ grid }}
        
            {{# Grid Text 1 - text #}}
            {{ grid_text_1 }}
            {{# Grid Text 2 - text #}}
            {{ grid_text_2 }}
            
        {{ /grid }}
        {{# Stacked Grid - grid #}}
        {{ stacked_grid }}
        
            {{# Stacked Grid Text 1 - text #}}
            {{ stacked_grid_text_1 }}
            {{# Stacked Grid Text 2 - text #}}
            {{ stacked_grid_text_2 }}
            
        {{ /stacked_grid }}
        {{# Hidden Field - hidden #}}
        {{ hidden_field }}
        {{# HTML Field - html #}}
        {{ html_field }}
        {{# Integer Field - integer #}}
        {{ integer_field }}
        {{# Link Field - link #}}
        {{ link_field }}
        {{# Markdown - markdown #}}
        {{ markdown }}
        {{# Radio - radio #}}
        {{ radio }}
        {{# Range Field - range #}}
        {{ range_field }}
        {{# Select Field - select #}}
        {{ select_field }}
        {{# Table - table #}}
        {{ table | table }}
        {{# Textarea - textarea #}}
        {{ textarea }}
        {{# Time - time #}}
{{ time }}
{{# Toggle - toggle #}}
{{ if toggle }} Yes. {{ /if }}
{{# Video - video #}}
{{ video }}
{{# Yaml - yaml #}}
{{ yaml:stacked_grid }}
    {{ stacked_grid_text_1 }}
    {{ stacked_grid_text_2 }}
{{ /yaml:stacked_grid }}

        {{ yaml }}
            {{ grid }}
                {{ grid_text_1 }}
                {{ grid_text_2 }}
            {{ /grid }}
        {{ /yaml }}
        
        
        {{ yaml:grid }}
            {{ grid_text_1 }}
            {{ grid_text_2 }}
        {{ /yaml:grid }}
        
    {{ /if }}
{{ /bard }}

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
{{ count }}
{{ next }}<random></random>
{{ previous }}
{{ /collection }}
{{ collection:count }}
{{ collection:next }}
{{ collection:previous }}
{{ dump }}
{{ form:create }}
{{ form:errors }}
{{ form:set }}
{{ form:submission }}
{{ form:submissions}}<{{ value }}>{{ /form:submissions }}
{{ form:success }}
{{ get_content }}
{{ get_error }}<random></random>
{{ get_errors }}{{ errors }}<{{ value }}>{{ /errors }}{{ /get_errors }}
{{ get_files }}<{{ value }}>{{ /get_files }}
{{ glide:batch }}
{{ increment:reset }}
Link: {{ link ?? 'no link' }}
{{ if link }}Link{{ else }}no link{{ /if }}
{{ locales }}<{{ value }}>{{ /locales }}
{{ loop }}<{{ value }}>{{ /loop }}
{{ markdown }}<{{ value }}>{{ /markdown }}
{{ mix }}
{{ nav:breadcrumbs }}<{{ value }}>{{ /nav:breadcrumbs }}
{{ oauth }}
{{ parent }}<{{ value }}>{{ /parent }}
{{ partial:exists }}<random></random>
{{ partial:if_exists }}
{{ redirect }}
{{ route }}
{{ search }}
{{ section }}
{{ session:dump }}
{{ session:flash }}  a  b  c  d  e  f  ghijkl-mnop
{{ session:flush }}
{{ session:forget }}<random></random>
{{ session:has }}
{{ session:set }}
{{ svg }}
{{ switch }}
Taxonomy: {{ taxonomy }}
<random></random>{{ translate }}
{{ user:can }}
{{ user:forgot_password_form }}
{{ user:in }}
{{ user:logout }}
{{ user:logout_url }}
{{ user:register_form }}
{{ user:reset_password_form }}
{{ users }}<{{ value }}>{{ /users }}
{{ yield }}
<start_of_nested_partial>
{{ %partial:prefixed }}
{{ asset }}
                {{ cache }}
                {{ collection }}
                {{ count }}
                {{ next }}
                {{ previous }}
                {{ /collection }}
                {{ collection:count }}
                {{ collection:next }}
                {{ collection:previous }}
                {{ dump }}
                            {{ form:create }}
                            {{ form:errors }}
                            {{ form:set }}
                    {{ form:submission }}
                     {{ form:submissions}}<{{ value }}>{{ /form:submissions }}
                    {{ form:success }}
                            {{ get_content }}
                {{ get_error }}
{{ get_errors }}{{ errors }}<{{ value }}>{{ /errors }}{{ /get_errors }}
{{ get_files }}<{{ value }}>{{ /get_files }}
{{ glide:batch }}
{{ increment:reset }}asdf
Link: {{ link ?? 'no link' }}
{{ locales }}<{{ value }}>{{ /locales }}
{{ loop }}<{{ value }}>{{ /loop }}
{{ markdown }}<{{ value }}>{{ /markdown }}
{{ mix }}
{{ nav:breadcrumbs }}<{{ value }}>{{ /nav:breadcrumbs }}
{{ oauth }}
{{ parent }}<{{ value }}>{{ /parent }}
{{ partial:exists }}
{{ partial:if_exists }}
{{ redirect }}
{{ route }}
{{ search }}
{{ section }}
{{ session:dump }}
{{ session:flash }}
{{ session:flush }}
{{ session:forget }}
{{ session:has }}
{{ session:set }}
{{ svg }}
{{ switch }}
Taxonomy: {{ taxonomy }}
{{ translate }}
{{ user:can }}
{{ user:forgot_password_form }}
{{ user:in }}
{{ user:logout }}
{{ user:logout_url }}
{{ user:register_form }}
{{ user:reset_password_form }}
{{ users }}<{{ value }}>{{ /users }}
{{ yield }}
{{ /%partial:prefixed }}
<end_of_nested_partial>
<start_of_push_to_section>
{{ %section:the_section }}
{{ asset }}
{{ cache }}
{{ collection }}
{{ count }}
{{ next }}
{{ previous }}
{{ /collection }}
{{ collection:count }}
{{ collection:next }}
{{ collection:previous }}
{{ dump }}
{{ form:create }}
{{ form:errors }}
{{ form:set }}
{{ form:submission }}
{{ form:submissions}}<{{ value }}><random></random>{{ /form:submissions }}
{{ form:success }}<random></random>
{{ get_content }}
{{ get_error }}
{{ get_errors }}{{ errors }}<{{ value }}>{{ /errors }}{{ /get_errors }}
{{ get_files }}<{{ value }}>{{ /get_files }}
{{ glide:batch }}
{{ increment:reset }}
Link: {{ link ?? 'no link' }}
{{ locales }}<{{ value }}>{{ /locales }}
{{ loop }}<{{ value }}>{{ /loop }}
{{ markdown }}<{{ value }}>{{ /markdown }}
{{ mix }}
{{ nav:breadcrumbs }}<{{ value }}>{{ /nav:breadcrumbs }}
{{ oauth }}
{{ parent }}<{{ value }}>{{ /parent }}
{{ partial:exists }}
{{ partial:if_exists }}
{{ redirect }}
{{ route }}
{{ search }}
{{ section }}
{{ session:dump }}
{{ session:flash }}
{{ session:flush }}
{{ session:forget }}
{{ session:has }}
{{ session:set }}
{{ svg }}
{{ switch }}<random></random>
Taxonomy: {{ taxonomy }}
{{ translate }}
{{ user:can }}
{{ user:forgot_password_form }}
{{ user:in }}
{{ user:logout }}
{{ user:logout_url }}
{{ user:register_form }}
{{ user:reset_password_form }}
{{ users }}<{{ value }}><random></random>{{ /users }}
{{ yield }}

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

{{ /%section:the_section }}

{{ if this == that }}Yes{{ elseif that == this }}Else{{ else }}No{{ /if }}
{{ unless true }}Inside Unless{{ /unless }}
            @{{ escaped }}
            
            {{ noparse }}
            {{ user:in }}
{{ user:logout }}
{{ user:logout_url }}
{{ user:register_form }}
{{ user:reset_password_form }}
         {{ /noparse }}
{{ collection:articles }}
        {{ title }}
                {{ entries as="something" }}
{{ /entries }}
        {{ /collection:articles }}
EOT;

        $result = (new DocumentTransformer())->load($template)->getTemplate();

        $this->assertSame($template, $result);
    }

    public function test_transformer_can_correct_html_encoded_content()
    {
        $template = <<<'EOT'
<p>{{ if 1 &gt; 3 }}Yes.{{ else }}No.{{ /if }}<br>{{ if 1 &lt; 3 &amp;&amp; true == true }}Yes.{{ else }}No.{{ /if }}<br>{{ if 3 &gt; 1 }}3 is bigger{{ /if }}<br>{{ now format=&quot;Y&quot; }}<br>Just some content</p>
EOT;

        $transformer = new DocumentTransformer();
        $reversed = $transformer->correct($template);

        $this->assertSame('<p>{{ if 1 > 3 }}Yes.{{ else }}No.{{ /if }}<br>{{ if 1 < 3 && true == true }}Yes.{{ else }}No.{{ /if }}<br>{{ if 3 > 1 }}3 is bigger{{ /if }}<br>{{ now format="Y" }}<br>Just some content</p>', $reversed);
    }
}
