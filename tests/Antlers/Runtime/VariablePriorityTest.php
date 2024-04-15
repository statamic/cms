<?php

namespace Tests\Antlers\Runtime;

use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Exceptions\TaxonomyNotFoundException;
use Statamic\Modifiers\ModifierNotFoundException;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class VariablePriorityTest extends ParserTestCase
{
    protected $data = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'collection' => [
                'articles' => [
                    ['title' => 'Test One'],
                    ['title' => 'Test Two'],
                ],
                'handle' => 'The Collection',
            ],
            'form' => [
                'handle' => 'The Form',
            ],
            'link' => 'Hello, there!',
            'taxonomy' => 'The Taxonomy',
        ];
    }

    private function render($template)
    {
        return $this->renderString($template, $this->data, true);
    }

    public function test_arrays_take_priority_over_tags()
    {
        $template = <<<'EOT'
{{ collection:articles }}<{{ title }}>{{ /collection:articles }}
EOT;

        $this->assertSame('<Test One><Test Two>', $this->render($template));
    }

    public function test_collection_tag_is_still_invoked()
    {
        $template = <<<'EOT'
{{ collection:news }}<{{ title }}>{{ /collection:news }}
EOT;
        $this->expectException(CollectionNotFoundException::class);
        $this->expectExceptionMessage('Collection [news] not found');
        $this->render($template);
    }

    public function test_strings_can_be_used_inside_tags_with_similar_names()
    {
        $template = <<<'EOT'
{{ collection from="{collection}" }}{{ /collection }}
EOT;

        $this->expectException(CollectionNotFoundException::class);
        $this->expectExceptionMessage('Collection [news] not found');
        $this->renderString($template, ['collection' => 'news'], true);
    }

    public function test_strings_can_override_tags_non_pair()
    {
        $this->assertSame('Hello, there!', $this->render('{{ link }}'));
    }

    public function test_strings_override_tags_even_with_tag_parameters()
    {
        $this->expectException(ModifierNotFoundException::class);
        $this->expectExceptionMessage('Modifier [to] not found');
        $this->render('{{ link to="the moon" }}');
    }

    public function test_taxonomy_can_be_used_as_variable_named()
    {
        $this->assertSame('The Taxonomy', $this->render('{{ taxonomy }}'));
    }

    public function test_taxonomy_tag_is_called()
    {
        $this->expectException(TaxonomyNotFoundException::class);
        $this->expectExceptionMessage('Taxonomy [tags] not found');
        $this->render('{{ taxonomy:tags }}{{ /taxonomy:tags }}');
    }

    public function test_common_variable_names_with_handles()
    {
        $this->assertSame('The Form', $this->render('{{ form:handle }}'));
        $this->assertSame('The Collection', $this->render('{{ collection:handle }}'));
    }

    public function test_form_tag_is_called()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Form with handle [iamaform] cannot be found.');
        $this->render('{{ form:create in="iamaform"}}');
    }

    public function test_similar_variable_names_are_prioritized_within_partials()
    {
        $this->expectException(CollectionNotFoundException::class);
        $this->expectExceptionMessage('Collection [notacollection] not found');
        $this->renderString('{{ partial:collection collection="{collection}" }}', ['collection' => 'notacollection'], true);
    }

    public function test_variables_take_priority_over_tags_stress_test()
    {
        $data = [
            'asset' => 'asset',
            'cache' => 'cache',
            'collection' => [
                'count' => 'collection:count',
                'next' => 'collection:next',
                'previous' => 'collection:previous',
            ],
            'dump' => 'dump',
            'form' => [
                'create' => 'create',
                'errors' => 'errors',
                'set' => 'set',
                'submission' => 'submission',
                'submissions' => [
                    'one',
                    'two',
                    'three',
                ],
                'success' => 'success',
            ],
            'get_content' => 'get_content',
            'get_error' => 'get_error',
            'get_errors' => [
                'errors' => [
                    'one',
                    'two',
                    'three',
                ],
            ],
            'get_files' => [
                'file_one',
                'file_two',
                'file_three',
            ],
            'glide' => [
                'batch' => 'batch',
            ],
            'increment' => [
                'reset' => 'reset',
            ],
            'link' => null,
            'locales' => [
                'locale_one',
                'locale_two',
                'locale_three',
            ],
            'loop' => [
                'loop_one',
                'loop_two',
                'loop_three',
            ],
            'markdown' => [
                'markdown_one',
                'markdown_two',
                'markdown_three',
            ],
            'mix' => 'mix',
            'nav' => [
                'breadcrumbs' => [
                    'breadcrumb_one',
                    'breadcrumb_two',
                    'breadcrumb_three',
                ],
            ],
            'oauth' => 'oauth',
            'obfuscate' => 'obfuscate',
            'parent' => [
                'parent_one',
                'parent_two',
                'parent_three',
            ],
            'partial' => [
                'exists' => 'partial:exists',
                'if_exists' => 'partial:if_exists',
            ],
            'redirect' => 'redirect',
            'route' => 'route',
            'search' => 'search',
            'section' => 'section',
            'session' => [
                'dump' => 'session:dump',
                'flash' => 'session:flash',
                'flush' => 'session:flush',
                'forget' => 'session:forget',
                'has' => 'session:has',
                'set' => 'session:set',
            ],
            'svg' => 'svg',
            'switch' => 'switch',
            'taxonomy' => collect(),
            'translate' => 'translate',
            'user' => [
                'can' => 'user:can',
                'forgot_password_form' => 'user:forgot_password_form',
                'in' => 'user:in',
                'logout' => 'user:logout',
                'logout_url' => 'user:logout_url',
                'register_form' => 'user:register_form',
                'reset_password_form' => 'user:reset_password_form',
            ],
            'users' => [
                'user_one',
                'user_two',
                'user_three',
            ],
            'yield' => 'yield',
        ];

        $template = <<<'EOT'
<start_of_section>
{{ %yield:the_section }}
<end_of_section>
<start_of_variables>
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
<end_of_variables>
{{ %partial:variables }}
EOT;

        $expected = <<<'EOT'
<start_of_section>

asset
cache

collection:count
collection:next
collection:previous

collection:count
collection:next
collection:previous
dump
create
errors
set
submission
<one><two><three>
success
get_content
get_error
<one><two><three>
<file_one><file_two><file_three>
batch
reset
Link: no link
<locale_one><locale_two><locale_three>
<loop_one><loop_two><loop_three>
<markdown_one><markdown_two><markdown_three>
mix
<breadcrumb_one><breadcrumb_two><breadcrumb_three>
oauth
<parent_one><parent_two><parent_three>
partial:exists
partial:if_exists
redirect
route
search
section
session:dump
session:flash
session:flush
session:forget
session:has
session:set
svg
switch
Taxonomy: 
translate
user:can
user:forgot_password_form
user:in
user:logout
user:logout_url
user:register_form
user:reset_password_form
<user_one><user_two><user_three>
yield

<end_of_section>
<start_of_variables>
asset
cache

collection:count
collection:next
collection:previous

collection:count
collection:next
collection:previous
dump
create
errors
set
submission
<one><two><three>
success
get_content
get_error
<one><two><three>
<file_one><file_two><file_three>
batch
reset
Link: no link
<locale_one><locale_two><locale_three>
<loop_one><loop_two><loop_three>
<markdown_one><markdown_two><markdown_three>
mix
<breadcrumb_one><breadcrumb_two><breadcrumb_three>
oauth
<parent_one><parent_two><parent_three>
partial:exists
partial:if_exists
redirect
route
search
section
session:dump
session:flash
session:flush
session:forget
session:has
session:set
svg
switch
Taxonomy: 
translate
user:can
user:forgot_password_form
user:in
user:logout
user:logout_url
user:register_form
user:reset_password_form
<user_one><user_two><user_three>
yield
<end_of_variables>
<start_of_variable_partial>
asset
cache

collection:count
collection:next
collection:previous

collection:count
collection:next
collection:previous
dump
create
errors
set
submission
<one><two><three>
success
get_content
get_error
<one><two><three>
<file_one><file_two><file_three>
batch
reset
Link: no link
no link
<locale_one><locale_two><locale_three>
<loop_one><loop_two><loop_three>
<markdown_one><markdown_two><markdown_three>
mix
<breadcrumb_one><breadcrumb_two><breadcrumb_three>
oauth
<parent_one><parent_two><parent_three>
partial:exists
partial:if_exists
redirect
route
search
section
session:dump
session:flash
session:flush
session:forget
session:has
session:set
svg
switch
Taxonomy: 
translate
user:can
user:forgot_password_form
user:in
user:logout
user:logout_url
user:register_form
user:reset_password_form
<user_one><user_two><user_three>
yield
<start_of_nested_partial>
asset
cache

collection:count
collection:next
collection:previous

collection:count
collection:next
collection:previous
dump
create
errors
set
submission
<one><two><three>
success
get_content
get_error
<one><two><three>
<file_one><file_two><file_three>
batch
reset
Link: no link
<locale_one><locale_two><locale_three>
<loop_one><loop_two><loop_three>
<markdown_one><markdown_two><markdown_three>
mix
<breadcrumb_one><breadcrumb_two><breadcrumb_three>
oauth
<parent_one><parent_two><parent_three>
partial:exists
partial:if_exists
redirect
route
search
section
session:dump
session:flash
session:flush
session:forget
session:has
session:set
svg
switch
Taxonomy: 
translate
user:can
user:forgot_password_form
user:in
user:logout
user:logout_url
user:register_form
user:reset_password_form
<user_one><user_two><user_three>
yield
<end_of_nested_partial>
<start_of_push_to_section>

<end_of_push_to_section>
<end_of_variable_partial>
EOT;

        // Double parse/assertion is to ensure that it doesn't confuse itself/properly resets state.
        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings((string) $this->parser()->parse($template, $data)));
        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings((string) $this->parser()->parse($template, $data)));

        $template = <<<'EOT'
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
EOT;

        $expected = <<<'EOT'
asset
cache

collection:count
collection:next
collection:previous

collection:count
collection:next
collection:previous
dump
create
errors
set
submission
<one><two><three>
success
get_content
get_error
<one><two><three>
<file_one><file_two><file_three>
batch
reset
Link: no link
<locale_one><locale_two><locale_three>
<loop_one><loop_two><loop_three>
<markdown_one><markdown_two><markdown_three>
mix
<breadcrumb_one><breadcrumb_two><breadcrumb_three>
oauth
<parent_one><parent_two><parent_three>
partial:exists
partial:if_exists
redirect
route
search
section
session:dump
session:flash
session:flush
session:forget
session:has
session:set
svg
switch
Taxonomy: 
translate
user:can
user:forgot_password_form
user:in
user:logout
user:logout_url
user:register_form
user:reset_password_form
<user_one><user_two><user_three>
yield
EOT;

        // Double parse/assertion is to ensure that it doesn't confuse itself/properly resets state.
        $this->assertSame($expected, (string) $this->parser()->parse($template, $data));
        $this->assertSame($expected, (string) $this->parser()->parse($template, $data));
    }
}
