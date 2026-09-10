<?php

namespace Tests\Tags\Form;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Facades\Form;
use Statamic\Fields\Fieldset;
use Statamic\Forms\SendEmails;
use Statamic\Statamic;

class FormCreateTest extends FormTestCase
{
    #[Test]
    public function it_renders_form()
    {
        $forms = [
            $this->tag('{{ form:create handle="contact" }}{{ /form:create }}'),
            $this->tag('{{ form:create is="contact" }}{{ /form:create }}'),
            $this->tag('{{ form:create in="contact" }}{{ /form:create }}'),
            $this->tag('{{ form:create form="contact" }}{{ /form:create }}'),
            $this->tag('{{ form:create formset="contact" }}{{ /form:create }}'),
            $this->tag('{{ form:contact }}{{ /form:contact }}'), // Shorthand
        ];

        $this->assertCount(6, $forms);

        foreach ($forms as $output) {
            $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/forms/contact">', $output);
            $this->assertStringContainsString(csrf_field(), $output);
            $this->assertStringEndsWith('</form>', $output);
        }
    }

    #[Test]
    public function it_renders_form_with_params()
    {
        $output = $this->tag('{{ form:contact redirect="/submitted" error_redirect="/errors" class="form" id="form" }}{{ /form:contact }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/forms/contact" class="form" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/submitted" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="/errors" />', $output);
    }

    #[Test]
    public function it_renders_form_with_redirects_to_anchor()
    {
        $output = $this->tag('{{ form:contact redirect="#form" error_redirect="#form" }}{{ /form:contact }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="http://localhost#form" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="http://localhost#form" />', $output);
    }

    #[Test]
    public function it_dynamically_renders_fields()
    {
        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:contact }}
    {{ form:fields }}
        <label>{{ display }}</label>{{ field }}
    {{ /form:fields }}
{{ /form:contact }}
EOT
        ));

        $this->assertStringContainsString('<label>Full Name</label><input id="contact-form-name-field" type="text" name="name" value="">', $output);
        $this->assertStringContainsString('<label>Email Address</label><input id="contact-form-email-field" type="email" name="email" value="" required>', $output);
        $this->assertStringContainsString('<label>Message</label><textarea id="contact-form-message-field" name="message" rows="5" required></textarea>', $output);

        preg_match_all('/<label>(.+)<\/label>/U', $output, $fieldOrder);

        $this->assertEquals(['Full Name', 'Email Address', 'Message'], $fieldOrder[1]);
    }

    #[Test]
    public function it_dynamically_renders_fields_with_scope_param()
    {
        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:contact }}
    {{ form:fields scope="field" }}
        <label>{{ field:display }}</label>{{ field:field }}
    {{ /form:fields }}
{{ /form:contact }}
EOT
        ));

        $this->assertStringContainsString('<label>Full Name</label><input id="contact-form-name-field" type="text" name="name" value="">', $output);
        $this->assertStringContainsString('<label>Email Address</label><input id="contact-form-email-field" type="email" name="email" value="" required>', $output);
        $this->assertStringContainsString('<label>Message</label><textarea id="contact-form-message-field" name="message" rows="5" required></textarea>', $output);

        preg_match_all('/<label>(.+)<\/label>/U', $output, $fieldOrder);

        $this->assertEquals(['Full Name', 'Email Address', 'Message'], $fieldOrder[1]);
    }

    #[Test]
    public function it_dynamically_renders_group_fields_recursively()
    {
        $this->createForm([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => 'Section One',
                            'instructions' => 'Section One Instructions',
                            'fields' => [
                                [
                                    'handle' => 'group_one',
                                    'field' => [
                                        'type' => 'group',
                                        'display' => 'Group One',
                                        'instructions' => 'Group One Instructions',
                                        'fields' => [
                                            [
                                                'handle' => 'alpha',
                                                'field' => [
                                                    'type' => 'text',
                                                ],
                                            ],
                                            [
                                                'handle' => 'bravo',
                                                'field' => [
                                                    'type' => 'text',
                                                    'display' => 'Bravo',
                                                    'instructions' => 'This field has instructions!',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], 'survey');

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:survey }}
    {{ sections }}
        <div class="section">{{ display }}{{ if instructions }} ({{ instructions }}){{ /if }}
            {{ form:fields }}
                <div class="field-in-section">{{ display ?: handle }}{{ if instructions }} ({{ instructions }}){{ /if }}</div>
                {{ field }}
            {{ /form:fields }}
        </div>
    {{ /sections }}
    <div class="fields">
        {{ form:fields }}
            <div class="field-by-itself">{{ display ?: handle }}{{ if instructions }} ({{ instructions }}){{ /if }}</div>
            {{ field }}
        {{ /form:fields }}
    </div>
{{ /form:survey }}
EOT
        ));

        $this->assertStringContainsString('<div class="section">Section One (Section One Instructions)', $output);

        $this->assertStringContainsString('<div class="field-in-section">Group One (Group One Instructions)', $output);
        $this->assertStringContainsString('<div class="field-by-itself">Group One (Group One Instructions)', $output);
        $this->assertStringContainsString('<div class="field-in-section">group_one.alpha</div>', $output);
        $this->assertStringContainsString('<div class="field-by-itself">group_one.alpha</div>', $output);
        $this->assertStringContainsString('<div class="field-in-section">Bravo (This field has instructions!)</div>', $output);
        $this->assertStringContainsString('<div class="field-by-itself">Bravo (This field has instructions!)</div>', $output);
    }

    #[Test]
    public function it_dynamically_renders_group_fields_recursively_with_scope_param()
    {
        $this->createForm([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => 'Section One',
                            'instructions' => 'Section One Instructions',
                            'fields' => [
                                [
                                    'handle' => 'group_one',
                                    'field' => [
                                        'type' => 'group',
                                        'display' => 'Group One',
                                        'instructions' => 'Group One Instructions',
                                        'fields' => [
                                            [
                                                'handle' => 'alpha',
                                                'field' => [
                                                    'type' => 'text',
                                                ],
                                            ],
                                            [
                                                'handle' => 'bravo',
                                                'field' => [
                                                    'type' => 'text',
                                                    'display' => 'Bravo',
                                                    'instructions' => 'This field has instructions!',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], 'survey');

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:survey }}
    {{ sections }}
        <div class="section">{{ display }}{{ if instructions }} ({{ instructions }}){{ /if }}
            {{ form:fields scope="field" }}
                <div class="field-in-section">{{ field:display ?: field:handle }}{{ if field:instructions }} ({{ field:instructions }}){{ /if }}</div>
                {{ field:field }}
            {{ /form:fields }}
        </div>
    {{ /sections }}
    <div class="fields">
        {{ form:fields scope="field" }}
            <div class="field-by-itself">{{ field:display ?: field:handle }}{{ if field:instructions }} ({{ field:instructions }}){{ /if }}</div>
            {{ field:field }}
        {{ /form:fields }}
    </div>
{{ /form:survey }}
EOT
        ));

        $this->assertStringContainsString('<div class="section">Section One (Section One Instructions)', $output);

        $this->assertStringContainsString('<div class="field-in-section">Group One (Group One Instructions)', $output);
        $this->assertStringContainsString('<div class="field-by-itself">Group One (Group One Instructions)', $output);
        $this->assertStringContainsString('<div class="field-in-section">group_one.alpha</div>', $output);
        $this->assertStringContainsString('<div class="field-by-itself">group_one.alpha</div>', $output);
        $this->assertStringContainsString('<div class="field-in-section">Bravo (This field has instructions!)</div>', $output);
        $this->assertStringContainsString('<div class="field-by-itself">Bravo (This field has instructions!)</div>', $output);
    }

    #[Test]
    public function it_dynamically_renders_fields_using_legacy_array()
    {
        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:contact }}
    {{ fields }}
        <label>{{ display }}</label>{{ field }}
    {{ /fields }}
{{ /form:contact }}
EOT
        ));

        $this->assertStringContainsString('<label>Full Name</label><input id="contact-form-name-field" type="text" name="name" value="">', $output);
        $this->assertStringContainsString('<label>Email Address</label><input id="contact-form-email-field" type="email" name="email" value="" required>', $output);
        $this->assertStringContainsString('<label>Message</label><textarea id="contact-form-message-field" name="message" rows="5" required></textarea>', $output);

        preg_match_all('/<label>(.+)<\/label>/U', $output, $fieldOrder);

        $this->assertEquals(['Full Name', 'Email Address', 'Message'], $fieldOrder[1]);
    }

    #[Test]
    public function it_dynamically_renders_specific_fields_using_params()
    {
        $this->createForm([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => 'Section One',
                            'fields' => [
                                ['handle' => 'first_name', 'field' => ['type' => 'text']],
                                ['handle' => 'middle_name', 'field' => ['type' => 'text', 'display' => 'Middle Name']],
                                ['handle' => 'last_name', 'field' => ['type' => 'text', 'display' => 'Last Name']],
                                [
                                    'handle' => 'group_one',
                                    'field' => [
                                        'type' => 'group',
                                        'display' => 'Group One',
                                        'fields' => [
                                            ['handle' => 'nested_one', 'field' => ['type' => 'text', 'display' => 'Nested One']],
                                            ['handle' => 'nested_two', 'field' => ['type' => 'text', 'display' => 'Nested Two']],
                                        ],
                                    ],
                                ],
                                [
                                    'handle' => 'group_two',
                                    'field' => [
                                        'type' => 'group',
                                        'display' => 'Group Two',
                                        'fields' => [
                                            ['handle' => 'nested_three', 'field' => ['type' => 'text', 'display' => 'Nested One']],
                                            ['handle' => 'nested_four', 'field' => ['type' => 'text', 'display' => 'Nested Two']],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], 'survey');

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:survey }}
    <div class="get-top-level-field">{{ form:fields get="middle_name" }}{{ handle }},{{ /form:fields }}</div>
    <div class="get-group-field">{{ form:fields get="group_one" }}{{ handle }},{{ /form:fields }}</div>
    <div class="get-nested-field">{{ form:fields get="group_one.nested_two" }}{{ handle }},{{ /form:fields }}</div>
    <div class="only-piped-fields">{{ form:fields only="middle_name|group_one" }}{{ handle }},{{ /form:fields }}</div>
    <div class="except-piped-fields">{{ form:fields except="middle_name|group_one" }}{{ handle }},{{ /form:fields }}</div>
{{ /form:survey }}
EOT
        ));

        $this->assertStringContainsString('<div class="get-top-level-field">middle_name,</div>', $output);
        $this->assertStringContainsString('<div class="get-group-field">group_one,</div>', $output);
        $this->assertStringContainsString('<div class="get-nested-field">group_one.nested_two,</div>', $output);
        $this->assertStringContainsString('<div class="only-piped-fields">middle_name,group_one,</div>', $output);
        $this->assertStringContainsString('<div class="except-piped-fields">first_name,last_name,group_two,</div>', $output);
    }

    #[Test]
    public function it_dynamically_renders_fields_with_form_handle()
    {
        foreach (['contact', 'contact-form', 'kontakt_formular'] as $handle) {
            $this->createForm(handle: $handle);
            $output = $this->normalizeHtml($this->tag('{{ form in="'.$handle.'" }}{{ form:fields }}{{ field }}{{ /form:fields }}{{ /form }}'));
            $formSlug = str_replace('_', '-', $handle);
            $this->assertStringContainsString('<input id="'.$formSlug.'-form-name-field"', $output);
            $this->assertStringContainsString('<input id="'.$formSlug.'-form-email-field"', $output);
            $this->assertStringContainsString('<textarea id="'.$formSlug.'-form-message-field"', $output);
        }
    }

    #[Test]
    public function it_dynamically_renders_fields_with_form_handle_using_legacy_array_syntax()
    {
        foreach (['contact', 'contact-form', 'kontakt_formular'] as $handle) {
            $this->createForm(handle: $handle);
            $output = $this->normalizeHtml($this->tag('{{ form in="'.$handle.'" }}{{ fields }}{{ field }}{{ /fields }}{{ /form }}'));
            $formSlug = str_replace('_', '-', $handle);
            $this->assertStringContainsString('<input id="'.$formSlug.'-form-name-field"', $output);
            $this->assertStringContainsString('<input id="'.$formSlug.'-form-email-field"', $output);
            $this->assertStringContainsString('<textarea id="'.$formSlug.'-form-message-field"', $output);
        }
    }

    #[Test]
    public function it_dynamically_renders_text_field()
    {
        $this->assertFieldRendersHtml([
            '<input id="[[form-handle]]-form-favourite-animal-field" type="text" name="favourite_animal" value="">',
        ], [
            'handle' => 'favourite_animal',
            'field' => [
                'type' => 'text',
            ],
        ]);

        $this->assertFieldRendersHtml([
            '<input id="[[form-handle]]-form-favourite-animal-field" type="text" name="favourite_animal" value="buffalo">',
        ], [
            'handle' => 'favourite_animal',
            'field' => [
                'type' => 'text',
            ],
        ], [
            'favourite_animal' => 'buffalo',
        ]);
    }

    #[Test]
    public function it_dynamically_renders_text_field_with_custom_input_type()
    {
        $this->assertFieldRendersHtml([
            '<input id="[[form-handle]]-form-age-field" type="number" name="age" value="">',
        ], [
            'handle' => 'age',
            'field' => [
                'type' => 'text',
                'input_type' => 'number',
            ],
        ]);

        $this->assertFieldRendersHtml([
            '<input id="[[form-handle]]-form-age-field" type="number" name="age" value="24">',
        ], [
            'handle' => 'age',
            'field' => [
                'type' => 'text',
                'input_type' => 'number',
            ],
        ], [
            'age' => 24,
        ]);
    }

    #[Test]
    public function it_dynamically_renders_textarea_field()
    {
        $this->assertFieldRendersHtml([
            '<textarea id="[[form-handle]]-form-comment-field" name="comment" rows="5"></textarea>',
        ], [
            'handle' => 'comment',
            'field' => [
                'type' => 'textarea',
            ],
        ]);

        $this->assertFieldRendersHtml([
            '<textarea id="[[form-handle]]-form-comment-field" name="comment" rows="5">Hey hoser!</textarea>',
        ], [
            'handle' => 'comment',
            'field' => [
                'type' => 'textarea',
            ],
        ], [
            'comment' => 'Hey hoser!',
        ]);
    }

    #[Test]
    public function it_dynamically_renders_checkboxes_field()
    {
        $this->assertFieldRendersHtml([
            '<label><input id="[[form-handle]]-form-favourite-animals-field-cat-option" type="checkbox" name="favourite_animals[]" value="cat">Cat</label>',
            '<br>',
            '<label><input id="[[form-handle]]-form-favourite-animals-field-armadillo-option" type="checkbox" name="favourite_animals[]" value="armadillo">Armadillo</label>',
            '<br>',
            '<label><input id="[[form-handle]]-form-favourite-animals-field-rat-option" type="checkbox" name="favourite_animals[]" value="rat">rat</label>',
        ], [
            'handle' => 'favourite_animals',
            'field' => [
                'type' => 'checkboxes',
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ]);

        $this->assertFieldRendersHtml([
            '<label><input id="[[form-handle]]-form-favourite-animals-field-cat-option" type="checkbox" name="favourite_animals[]" value="cat" checked>Cat</label>',
            '<br>',
            '<label><input id="[[form-handle]]-form-favourite-animals-field-armadillo-option" type="checkbox" name="favourite_animals[]" value="armadillo">Armadillo</label>',
            '<br>',
            '<label><input id="[[form-handle]]-form-favourite-animals-field-rat-option" type="checkbox" name="favourite_animals[]" value="rat" checked>rat</label>',
        ], [
            'handle' => 'favourite_animals',
            'field' => [
                'type' => 'checkboxes',
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ], [
            'favourite_animals' => ['cat', 'rat'],
        ]);
    }

    #[Test]
    public function it_dynamically_renders_inline_checkboxes_field()
    {
        $this->assertFieldRendersHtml([
            '<label><input id="[[form-handle]]-form-favourite-animals-field-cat-option" type="checkbox" name="favourite_animals[]" value="cat">Cat</label>',
            '<label><input id="[[form-handle]]-form-favourite-animals-field-armadillo-option" type="checkbox" name="favourite_animals[]" value="armadillo">Armadillo</label>',
            '<label><input id="[[form-handle]]-form-favourite-animals-field-rat-option" type="checkbox" name="favourite_animals[]" value="rat">rat</label>',
        ], [
            'handle' => 'favourite_animals',
            'field' => [
                'type' => 'checkboxes',
                'inline' => true,
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ]);

        $this->assertFieldRendersHtml([
            '<label><input id="[[form-handle]]-form-favourite-animals-field-cat-option" type="checkbox" name="favourite_animals[]" value="cat" checked>Cat</label>',
            '<label><input id="[[form-handle]]-form-favourite-animals-field-armadillo-option" type="checkbox" name="favourite_animals[]" value="armadillo">Armadillo</label>',
            '<label><input id="[[form-handle]]-form-favourite-animals-field-rat-option" type="checkbox" name="favourite_animals[]" value="rat" checked>rat</label>',
        ], [
            'handle' => 'favourite_animals',
            'field' => [
                'type' => 'checkboxes',
                'inline' => true,
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ], [
            'favourite_animals' => ['cat', 'rat'],
        ]);
    }

    #[Test]
    public function it_dynamically_renders_radio_field()
    {
        $this->assertFieldRendersHtml([
            '<label><input id="[[form-handle]]-form-favourite-animal-field-cat-option" type="radio" name="favourite_animal" value="cat">Cat</label>',
            '<br>',
            '<label><input id="[[form-handle]]-form-favourite-animal-field-armadillo-option" type="radio" name="favourite_animal" value="armadillo">Armadillo</label>',
            '<br>',
            '<label><input id="[[form-handle]]-form-favourite-animal-field-rat-option" type="radio" name="favourite_animal" value="rat">rat</label>',
        ], [
            'handle' => 'favourite_animal',
            'field' => [
                'type' => 'radio',
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ]);

        $this->assertFieldRendersHtml([
            '<label><input id="[[form-handle]]-form-favourite-animal-field-cat-option" type="radio" name="favourite_animal" value="cat">Cat</label>',
            '<br>',
            '<label><input id="[[form-handle]]-form-favourite-animal-field-armadillo-option" type="radio" name="favourite_animal" value="armadillo" checked>Armadillo</label>',
            '<br>',
            '<label><input id="[[form-handle]]-form-favourite-animal-field-rat-option" type="radio" name="favourite_animal" value="rat">rat</label>',
        ], [
            'handle' => 'favourite_animal',
            'field' => [
                'type' => 'radio',
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ], [
            'favourite_animal' => 'armadillo',
        ]);
    }

    #[Test]
    public function it_dynamically_renders_inline_radio_field()
    {
        $this->assertFieldRendersHtml([
            '<label><input id="[[form-handle]]-form-favourite-animal-field-cat-option" type="radio" name="favourite_animal" value="cat">Cat</label>',
            '<label><input id="[[form-handle]]-form-favourite-animal-field-armadillo-option" type="radio" name="favourite_animal" value="armadillo">Armadillo</label>',
            '<label><input id="[[form-handle]]-form-favourite-animal-field-rat-option" type="radio" name="favourite_animal" value="rat">rat</label>',
        ], [
            'handle' => 'favourite_animal',
            'field' => [
                'type' => 'radio',
                'inline' => true,
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ]);

        $this->assertFieldRendersHtml([
            '<label><input id="[[form-handle]]-form-favourite-animal-field-cat-option" type="radio" name="favourite_animal" value="cat">Cat</label>',
            '<label><input id="[[form-handle]]-form-favourite-animal-field-armadillo-option" type="radio" name="favourite_animal" value="armadillo" checked>Armadillo</label>',
            '<label><input id="[[form-handle]]-form-favourite-animal-field-rat-option" type="radio" name="favourite_animal" value="rat">rat</label>',
        ], [
            'handle' => 'favourite_animal',
            'field' => [
                'type' => 'radio',
                'inline' => true,
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ], [
            'favourite_animal' => 'armadillo',
        ]);
    }

    #[Test]
    public function it_dynamically_renders_select_field()
    {
        $this->assertFieldRendersHtml([
            '<select id="[[form-handle]]-form-favourite-animal-field" name="favourite_animal">',
            '<option value>Please select...</option>',
            '<option value="cat">Cat</option>',
            '<option value="armadillo">Armadillo</option>',
            '<option value="rat">rat</option>',
            '</select>',
        ], [
            'handle' => 'favourite_animal',
            'field' => [
                'type' => 'select',
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ]);

        $this->assertFieldRendersHtml([
            '<select id="[[form-handle]]-form-favourite-animal-field" name="favourite_animal">',
            '<option value>Please select...</option>',
            '<option value="cat" selected>Cat</option>',
            '<option value="armadillo">Armadillo</option>',
            '<option value="rat">rat</option>',
            '</select>',
        ], [
            'handle' => 'favourite_animal',
            'field' => [
                'type' => 'select',
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ], [
            'favourite_animal' => 'cat',
        ]);
    }

    #[Test]
    public function it_dynamically_renders_multiple_select_field()
    {
        $this->assertFieldRendersHtml([
            '<select id="[[form-handle]]-form-favourite-animals-field" name="favourite_animals[]" multiple>',
            '<option value="cat">Cat</option>',
            '<option value="armadillo">Armadillo</option>',
            '<option value="rat">rat</option>',
            '</select>',
        ], [
            'handle' => 'favourite_animals',
            'field' => [
                'type' => 'select',
                'multiple' => true,
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ]);

        $this->assertFieldRendersHtml([
            '<select id="[[form-handle]]-form-favourite-animals-field" name="favourite_animals[]" multiple>',
            '<option value="cat" selected>Cat</option>',
            '<option value="armadillo">Armadillo</option>',
            '<option value="rat" selected>rat</option>',
            '</select>',
        ], [
            'handle' => 'favourite_animals',
            'field' => [
                'type' => 'select',
                'multiple' => true,
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => null, // label should fall back to value
                ],
            ],
        ], [
            'favourite_animals' => ['cat', 'rat'],
        ]);
    }

    #[Test]
    public function it_dynamically_renders_upload_field()
    {
        $this->assertFieldRendersHtml([
            '<input id="[[form-handle]]-form-cat-selfie-field" type="file" name="cat_selfie">',
        ], [
            'handle' => 'cat_selfie',
            'field' => [
                'type' => 'upload',
                'display' => 'Cat Selfie',
                'max_files' => 1,
            ],
        ]);
    }

    #[Test]
    public function it_dynamically_renders_multiple_upload_field()
    {
        $this->assertFieldRendersHtml([
            '<input id="[[form-handle]]-form-cat-selfies-field" type="file" name="cat_selfies[]" multiple>',
        ], [
            'handle' => 'cat_selfies',
            'field' => [
                'type' => 'upload',
                'display' => 'Cat Selfies',
            ],
        ]);
    }

    #[Test]
    public function it_dynamically_renders_field_with_fallback_to_default_partial()
    {
        $this->assertFieldRendersHtml([
            '<input id="[[form-handle]]-form-custom-field" type="text" name="custom" value="">',
        ], [
            'handle' => 'custom',
            'field' => [
                'type' => 'markdown', // 'markdown' doesn't have a template, so it should fall back to default.antlers.html
            ],
        ]);

        $this->assertFieldRendersHtml([
            '<input id="[[form-handle]]-form-custom-field" type="text" name="custom" value="fall back to default partial">',
        ], [
            'handle' => 'custom',
            'field' => [
                'type' => 'markdown',
            ],
        ], [
            'custom' => 'fall back to default partial',
        ]);
    }

    #[Test]
    public function it_dynamically_renders_pages_array()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->createForm([
            'pages' => [
                [
                    'id' => 'page_one',
                    'display' => 'Page One',
                    'instructions' => 'Page One Instructions',
                    'sections' => [
                        ['display' => 'Section A', 'fields' => [['handle' => 'name', 'field' => ['type' => 'text']]]],
                    ],
                ],
                [
                    'id' => 'page_two',
                    'display' => 'Page Two',
                    'previous_page_label' => 'Back',
                    'sections' => [
                        ['display' => 'Section B', 'fields' => [['handle' => 'email', 'field' => ['type' => 'text']]]],
                    ],
                ],
            ],
        ], 'survey');

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:survey }}
    {{ pages }}
        <div class="page">{{ id }} - {{ display }}{{ if instructions }} ({{ instructions }}){{ /if }}{{ if previous_page_label }} - back:{{ previous_page_label }}{{ /if }} - button:{{ button_label }} - {{ sections }}[{{ display }}:{{ fields }}{{ handle }},{{ /fields }}]{{ /sections }}</div>
    {{ /pages }}
{{ /form:survey }}
EOT
        ));

        // button_label should default to "Next", then "Submit" on the last page.
        // The back button is output when a previous_page_label is set or show_previous_button is true.
        $this->assertStringContainsString('<div class="page">page_one - Page One (Page One Instructions) - button:Next - [Section A:name,]</div>', $output);
        $this->assertStringContainsString('<div class="page">page_two - Page Two - back:Back - button:Submit - [Section B:email,]</div>', $output);
    }

    #[Test]
    public function it_outputs_previous_page_label_when_show_previous_button_is_enabled()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->createForm([
            'pages' => [
                [
                    'id' => 'page_one',
                    'sections' => [
                        ['display' => 'Section A', 'fields' => [['handle' => 'name', 'field' => ['type' => 'text']]]],
                    ],
                ],
                [
                    'id' => 'page_two',
                    'show_previous_button' => true,
                    'sections' => [
                        ['display' => 'Section B', 'fields' => [['handle' => 'email', 'field' => ['type' => 'text']]]],
                    ],
                ],
            ],
        ], 'survey');

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:survey }}
    {{ pages }}
        <div class="page">{{ id }}{{ if previous_page_label }} - back:{{ previous_page_label }}{{ /if }} - button:{{ button_label }}</div>
    {{ /pages }}
{{ /form:survey }}
EOT
        ));

        $this->assertStringContainsString('<div class="page">page_one - button:Next</div>', $output);
        $this->assertStringContainsString('<div class="page">page_two - back:Previous Page - button:Submit</div>', $output);
    }

    #[Test]
    public function it_dynamically_renders_simplified_pages_array_when_forms_pro_is_not_installed()
    {
        // When forms-pro isn't installed, sections will be collapsed under a single page.
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);

        $this->createForm([
            'sections' => [
                ['display' => 'Section A', 'fields' => [['handle' => 'name', 'field' => ['type' => 'text']]]],
                ['display' => 'Section B', 'fields' => [['handle' => 'email', 'field' => ['type' => 'text']]]],
            ],
        ], 'survey');

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:survey }}
    {{ pages }}
        <div class="page">{{ id }}{{ if previous_page_label }} - back:{{ previous_page_label }}{{ /if }} - button:{{ button_label }} - {{ sections }}[{{ display }}:{{ fields }}{{ handle }},{{ /fields }}]{{ /sections }}</div>
    {{ /pages }}
    <div class="all-sections">{{ sections }}{{ display }},{{ /sections }}</div>
{{ /form:survey }}
EOT
        ));

        $this->assertStringContainsString('<div class="page">main - button:Submit - [Section A:name,][Section B:email,]</div>', $output);
    }

    #[Test]
    public function it_outputs_the_current_page()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->createForm([
            'pages' => [
                [
                    'id' => 'page_one',
                    'display' => 'Page One',
                    'instructions' => 'Page One Instructions',
                    'sections' => [
                        ['fields' => [['handle' => 'name', 'field' => ['type' => 'text']]]],
                    ],
                ],
                [
                    'id' => 'page_two',
                    'display' => 'Page Two',
                    'previous_page_label' => 'Back',
                    'sections' => [
                        ['fields' => [['handle' => 'email', 'field' => ['type' => 'text']]]],
                    ],
                ],
            ],
        ], 'survey');

        $template = <<<'EOT'
{{ form:survey }}
    <div class="page">{{ page:id }} - {{ page:display }}{{ if page:instructions }} ({{ page:instructions }}){{ /if }}{{ if page:previous_page_label }} - back:{{ page:previous_page_label }}{{ /if }} - button:{{ page:button_label }}</div>
{{ /form:survey }}
EOT;

        // Defaults to the first page; its button label is "Next" and there's no back button.
        $output = $this->normalizeHtml($this->tag($template));
        $this->assertStringContainsString('<div class="page">page_one - Page One (Page One Instructions) - button:Next</div>', $output);

        // Reflects the page query param; the last page's button label becomes "Submit".
        request()->merge(['page' => 'page_two']);
        $output = $this->normalizeHtml($this->tag($template));
        $this->assertStringContainsString('<div class="page">page_two - Page Two - back:Back - button:Submit</div>', $output);
    }

    #[Test]
    public function it_dynamically_renders_sections_array()
    {
        $this->createForm([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => 'One',
                            'instructions' => 'One Instructions',
                            'fields' => [
                                ['handle' => 'alpha', 'field' => ['type' => 'text']],
                                ['handle' => 'bravo', 'field' => ['type' => 'text']],
                            ],
                        ],
                        [
                            'display' => 'Two',
                            'instructions' => 'Two Instructions',
                            'fields' => [
                                ['handle' => 'charlie', 'field' => ['type' => 'text']],
                                ['handle' => 'delta', 'field' => ['type' => 'text']],
                            ],
                        ],
                        [
                            'display' => null,
                            'instructions' => null,
                            'fields' => [
                                ['handle' => 'echo', 'field' => ['type' => 'text']],
                                ['handle' => 'fox', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
            ],
        ], 'survey');

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:survey }}
    {{ sections }}
        <div class="section-fields-tag">{{ if display }}{{ display }} - {{ /if }}{{ if instructions }}{{ instructions }} - {{ /if }}{{ form:fields }}{{ handle }},{{ /form:fields }}</div>
        <div class="section-fields-array">{{ if display }}{{ display }} - {{ /if }}{{ if instructions }}{{ instructions }} - {{ /if }}{{ fields }}{{ handle }},{{ /fields }}</div>
    {{ /sections }}
    <div class="fields-tag">{{ form:fields }}{{ handle }},{{ /form:fields }}</div>
    <div class="fields-array">{{ fields }}{{ handle }},{{ /fields }}</div>
{{ /form:survey }}
EOT
        ));

        // Assert this all works with suggested `{{ form:fields }}` tag
        $this->assertStringContainsString('<div class="section-fields-tag">One - One Instructions - alpha,bravo,</div>', $output);
        $this->assertStringContainsString('<div class="section-fields-tag">Two - Two Instructions - charlie,delta,</div>', $output);
        $this->assertStringContainsString('<div class="section-fields-tag">echo,fox,</div>', $output);

        // Assert this all works with legacy `{{ fields }}` array for backwards compatibility as well
        // In reality, there's nothing wrong with this, but the tag supports extra sugar like recursion
        $this->assertStringContainsString('<div class="section-fields-array">One - One Instructions - alpha,bravo,</div>', $output);
        $this->assertStringContainsString('<div class="section-fields-array">Two - Two Instructions - charlie,delta,</div>', $output);
        $this->assertStringContainsString('<div class="section-fields-array">echo,fox,</div>', $output);

        // Even though the fields are all nested within sections,
        // we should still be able to get all of them via tag or array at top level...
        $this->assertStringContainsString('<div class="fields-tag">alpha,bravo,charlie,delta,echo,fox,</div>', $output);
        $this->assertStringContainsString('<div class="fields-array">alpha,bravo,charlie,delta,echo,fox,</div>', $output);
    }

    #[Test]
    public function it_renders_section_instructions_without_cascading_into_field_instructions()
    {
        $this->createForm([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => 'One',
                            'instructions' => 'One Instructions',
                            'fields' => [
                                ['handle' => 'alpha', 'field' => ['type' => 'text']],
                                ['handle' => 'bravo', 'field' => ['type' => 'text', 'instructions' => 'This field has instructions!']],
                            ],
                        ],
                    ],
                ],
            ],
        ], 'survey');

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:survey }}
    {{ sections }}
        <div class="section">{{ display }}{{ if instructions }} ({{ instructions }}){{ /if }}
            {{ form:fields }}
                <div class="tag-field-in-section">{{ handle }}{{ if instructions }} ({{ instructions }}){{ /if }}</div>
            {{ /form:fields }}
            {{ fields }}
                <div class="array-field-in-section">{{ handle }}{{ if instructions }} ({{ instructions }}){{ /if }}</div>
            {{ /fields }}
        </div>
    {{ /sections }}
    <div class="fields">
        {{ form:fields }}
            <div class="tag-field-by-itself">{{ handle }}{{ if instructions }} ({{ instructions }}){{ /if }}</div>
        {{ /form:fields }}
        {{ fields }}
            <div class="array-field-by-itself">{{ handle }}{{ if instructions }} ({{ instructions }}){{ /if }}</div>
        {{ /fields }}
    </div>
{{ /form:survey }}
EOT
        ));

        $this->assertStringContainsString('<div class="section">One (One Instructions)', $output);

        // Section instructions should NOT cascade down into field instructions with suggested `{{ form:fields }}` tag...
        $this->assertStringContainsString('<div class="tag-field-in-section">alpha</div>', $output);
        $this->assertStringContainsString('<div class="tag-field-by-itself">alpha</div>', $output);
        $this->assertStringContainsString('<div class="tag-field-in-section">bravo (This field has instructions!)</div>', $output);
        $this->assertStringContainsString('<div class="tag-field-by-itself">bravo (This field has instructions!)</div>', $output);

        // Assert this all works with legacy `{{ fields }}` array for backwards compatibility as well
        // In reality, there's nothing wrong with this, but the tag supports extra sugar like recursion
        $this->assertStringContainsString('<div class="array-field-in-section">alpha</div>', $output);
        $this->assertStringContainsString('<div class="array-field-by-itself">alpha</div>', $output);
        $this->assertStringContainsString('<div class="array-field-in-section">bravo (This field has instructions!)</div>', $output);
        $this->assertStringContainsString('<div class="array-field-by-itself">bravo (This field has instructions!)</div>', $output);
    }

    #[Test]
    public function it_wont_submit_form_and_renders_errors()
    {
        $this->assertEmpty(Form::find('contact')->submissions());

        $this
            ->post('/!/forms/contact', [
                'name' => '',
                'email' => '',
                'message' => '',
            ])
            ->assertSessionHasErrors(['email', 'message'], null, 'form.contact')
            ->assertLocation('/');

        $this->assertEmpty(Form::find('contact')->submissions());

        $output = $this->tag(<<<'EOT'
{{ form:contact }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="email-error">{{ error:email }}</p>
    <p class="success">{{ success }}</p>
{{ /form:contact }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="email-error">(.+)<\/p>/U', $output, $emailError);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $expected = [
            'The Email Address field is required.',
            'The Message field is required.',
        ];

        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expected[0], $emailError[1][0]);
        $this->assertEmpty($success[1]);
    }

    #[Test]
    public function it_will_submit_form_and_render_success()
    {
        $this->assertEmpty(Form::find('contact')->submissions());

        $this
            ->post('/!/forms/contact', [
                'email' => 'san@holo.com',
                'message' => 'hello',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $this->assertCount(1, Form::find('contact')->submissions());

        $output = $this->tag(<<<'EOT'
{{ form:contact }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /form:contact }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEmpty($errors[1]);
        $this->assertEquals(['Submission successful.'], $success[1]);
    }

    #[Test]
    public function it_only_outputs_the_success_message_after_the_final_page()
    {
        $this->createMultiPageForm();
        Form::find('survey')->save();

        $template = <<<'EOT'
{{ form:survey }}
    <p class="success">{{ success }}</p>
{{ /form:survey }}
EOT;

        // Submitting a non-final page advances without outputting the success message.
        $this
            ->post('/!/forms/survey', ['_page' => 'page_one', 'name' => 'Olaf'])
            ->assertSessionHasNoErrors();

        preg_match_all('/<p class="success">(.+)<\/p>/U', $this->tag($template), $success);
        $this->assertEmpty($success[1]);

        // Submitting the final page outputs the success message.
        $this
            ->post('/!/forms/survey', ['_page' => 'page_two', 'email' => 'olaf@example.com'])
            ->assertSessionHasNoErrors();

        preg_match_all('/<p class="success">(.+)<\/p>/U', $this->tag($template), $success);
        $this->assertEquals(['Submission successful.'], $success[1]);

        Form::find('survey')->submissions()->each->delete();
    }

    #[Test]
    public function it_follows_page_logic_to_a_rules_destination_on_submit()
    {
        $this->createMultiPageFormWithLogic();
        Form::find('survey')->save();

        // name=Olaf satisfies page one's rule, jumping straight to page three.
        $this
            ->from('/survey')
            ->post('/!/forms/survey', ['_page' => 'page_one', 'name' => 'Olaf'])
            ->assertSessionHasNoErrors()
            ->assertRedirectContains('page=page_three');

        Form::find('survey')->submissions()->each->delete();
    }

    #[Test]
    public function it_sends_you_back_to_the_first_page_when_jumping_straight_past_a_required_field()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->createForm([
            'pages' => [
                [
                    'id' => 'page_one',
                    'sections' => [['fields' => [['handle' => 'name', 'field' => ['type' => 'text', 'validate' => 'required']]]]],
                ],
                [
                    'id' => 'page_two',
                    'sections' => [['fields' => [['handle' => 'email', 'field' => ['type' => 'text']]]]],
                ],
            ],
        ], 'survey');
        Form::find('survey')->save();

        // Jump straight to the final page, skipping page one's required field.
        $this
            ->from('/survey')
            ->post('/!/forms/survey', ['_page' => 'page_two', 'email' => 'olaf@example.com'])
            ->assertSessionHasNoErrors()
            ->assertRedirectContains('page=page_one');

        // The submission wasn't finalized; it's still partial.
        $submissions = Form::find('survey')->submissions();
        $this->assertCount(1, $submissions);
        $this->assertTrue($submissions->first()->isPartial());

        Form::find('survey')->submissions()->each->delete();
    }

    #[Test]
    public function it_does_not_redirect_to_an_external_url_from_the_referrer_between_pages()
    {
        $this->createMultiPageForm();
        Form::find('survey')->save();

        // A forged referrer pointing off-site must not become the next-page redirect target.
        $response = $this
            ->from('https://evil.example/phishing')
            ->post('/!/forms/survey', ['_page' => 'page_one', 'name' => 'Olaf'])
            ->assertSessionHasNoErrors();

        $this->assertStringNotContainsString('evil.example', $response->headers->get('Location'));
        $response->assertRedirectContains('page=page_two');

        Form::find('survey')->submissions()->each->delete();
    }

    #[Test]
    public function the_previous_page_url_follows_the_path_taken_through_page_logic()
    {
        $this->createMultiPageFormWithLogic();

        $form = Form::find('survey');
        $form->save();

        // The user reached page three by jumping from page one (skipping page two).
        $submission = tap($form->makeSubmission()->data(['name' => 'Olaf'])->asPartial())->save();
        session()->put('form.survey.partial_submission', $submission->id());

        request()->merge(['page' => 'page_three']);

        $output = $this->tag('{{ form:survey }}{{ previous_page_url }}{{ /form:survey }}');

        // "Back" returns to page one — the page actually visited — not page two.
        $this->assertStringContainsString('page=page_one', $output);
        $this->assertStringNotContainsString('page=page_two', $output);

        $form->submissions()->each->delete();
    }

    private function createMultiPageFormWithLogic($handle = 'survey')
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->createForm([
            'pages' => [
                [
                    'id' => 'page_one',
                    'rules' => [[
                        'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Olaf']],
                        'destination' => 'page_three',
                    ]],
                    'sections' => [['fields' => [['handle' => 'name', 'field' => ['type' => 'text']]]]],
                ],
                [
                    'id' => 'page_two',
                    'sections' => [['fields' => [['handle' => 'colour', 'field' => ['type' => 'text']]]]],
                ],
                [
                    'id' => 'page_three',
                    'previous_page_label' => 'Back',
                    'sections' => [['fields' => [['handle' => 'email', 'field' => ['type' => 'text']]]]],
                ],
            ],
        ], $handle);
    }

    #[Test]
    public function it_will_submit_form_and_follow_custom_redirect_with_success()
    {
        $this->assertEmpty(Form::find('contact')->submissions());

        $this
            ->post('/!/forms/contact', [
                'email' => 'san@holo.com',
                'message' => 'hello',
                '_redirect' => '/submission-successful',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/submission-successful');

        $this->assertCount(1, Form::find('contact')->submissions());

        $output = $this->tag(<<<'EOT'
{{ form:contact }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
    {{ if submission_created }}
        <div class="analytics"></div>
    {{ /if }}
{{ /form:contact }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEmpty($errors[1]);
        $this->assertEquals(['Submission successful.'], $success[1]);
        $this->assertStringContainsString('<div class="analytics"></div>', $output);
    }

    #[Test]
    public function it_does_not_follow_external_redirect_on_success()
    {
        $this->assertEmpty(Form::find('contact')->submissions());

        $this
            ->from('/contact')
            ->post('/!/forms/contact', [
                'email' => 'san@holo.com',
                'message' => 'hello',
                '_redirect' => 'https://evil.com/phishing',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/contact');

        $this->assertCount(1, Form::find('contact')->submissions());
    }

    #[Test]
    public function it_will_submit_form_with_honeypot_filled_and_render_fake_success()
    {
        $this->assertEmpty(Form::find('contact')->submissions());

        $this
            ->post('/!/forms/contact', [
                'email' => 'san@holo.com',
                'message' => 'hello',
                'winnie' => 'the pooh',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $this->assertCount(0, Form::find('contact')->submissions());

        $output = $this->tag(<<<'EOT'
{{ form:contact }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
    {{ if submission_created }}
        <div class="analytics"></div>
    {{ /if }}
{{ /form:contact }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEmpty($errors[1]);
        $this->assertEquals(['Submission successful.'], $success[1]);
        $this->assertStringNotContainsString('<div class="analytics"></div>', $output);
    }

    #[Test]
    public function it_will_store_the_submission_as_spam_when_the_honeypot_is_filled_and_configured_to_do_so()
    {
        Form::find('contact')->data(['honeypot_behavior' => 'mark_as_spam'])->save();

        $this
            ->post('/!/forms/contact', [
                'email' => 'san@holo.com',
                'message' => 'hello',
                'winnie' => 'the pooh',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $submissions = Form::find('contact')->submissions();

        $this->assertCount(1, $submissions);
        $this->assertEquals('spam', $submissions->first()->status());
    }

    #[Test]
    public function it_forgets_the_partial_submission_when_it_is_stored_as_spam()
    {
        $this->createMultiPageForm();
        Form::find('survey')->data(['honeypot_behavior' => 'mark_as_spam'])->save();

        $this
            ->post('/!/forms/survey', ['_page' => 'page_one', 'name' => 'Olaf'])
            ->assertSessionHas('form.survey.partial_submission');

        // Tripping the honeypot on the final page stores the submission as spam, and the
        // session ends up in the same state as a successful submission would leave it.
        $this
            ->post('/!/forms/survey', ['_page' => 'page_two', 'email' => 'olaf@example.com', 'winnie' => 'the pooh'])
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('form.survey.partial_submission');

        $submissions = Form::find('survey')->submissions();

        $this->assertCount(1, $submissions);
        $this->assertEquals('spam', $submissions->first()->status());

        Form::find('survey')->submissions()->each->delete();
    }

    #[Test]
    public function it_will_render_fake_success_when_a_listener_throws_a_bare_silent_failure_exception()
    {
        Event::listen(\Statamic\Events\FormSubmitted::class, function () {
            throw new \Statamic\Exceptions\SilentFormFailureException;
        });

        $this
            ->post('/!/forms/contact', [
                'email' => 'san@holo.com',
                'message' => 'hello',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $this->assertCount(0, Form::find('contact')->submissions());
    }

    #[Test]
    public function it_wont_submit_form_and_follow_custom_redirect_with_errors()
    {
        $this->assertEmpty(Form::find('contact')->submissions());

        $this
            ->post('/!/forms/contact', [
                '_error_redirect' => '/submission-error',
                'name' => '',
                'email' => '',
                'message' => '',
            ])
            ->assertSessionHasErrors(['email', 'message'], null, 'form.contact')
            ->assertLocation('/submission-error');

        $this->assertCount(0, Form::find('contact')->submissions());

        $output = $this->tag(<<<'EOT'
{{ form:contact }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /form:contact }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $expected = [
            'The Email Address field is required.',
            'The Message field is required.',
        ];

        $this->assertEquals($expected, $errors[1]);
        $this->assertEmpty($success[1]);
    }

    #[Test]
    public function it_does_not_follow_external_error_redirect()
    {
        $this->assertEmpty(Form::find('contact')->submissions());

        Event::listen(function (\Statamic\Events\FormSubmitted $event) {
            throw ValidationException::withMessages(['custom' => 'This is a custom message']);
        });

        $this
            ->from('/contact')
            ->post('/!/forms/contact', [
                '_error_redirect' => 'https://evil.com/phishing',
                'name' => 'Hansolo',
                'email' => 'san@holo.com',
                'message' => 'hello',
            ])
            ->assertSessionHasErrors(['custom'], null, 'form.contact')
            ->assertLocation('/contact');

        $this->assertCount(0, Form::find('contact')->submissions());
    }

    #[Test]
    public function it_will_use_redirect_query_param_off_url()
    {
        $this->get('/?redirect=submission-successful&error_redirect=submission-failure');

        $expectedRedirect = '<input type="hidden" name="_redirect" value="submission-successful" />';
        $expectedErrorRedirect = '<input type="hidden" name="_error_redirect" value="submission-failure" />';

        $output = $this->tag('{{ form:contact }}{{ /form:contact }}');

        $this->assertStringNotContainsString($expectedRedirect, $output);
        $this->assertStringNotContainsString($expectedErrorRedirect, $output);

        $output = $this->tag('{{ form:contact allow_request_redirect="true" }}{{ /form:contact }}');

        $this->assertStringContainsString($expectedRedirect, $output);
        $this->assertStringContainsString($expectedErrorRedirect, $output);
    }

    #[Test]
    public function it_can_render_an_inline_error_when_multiple_rules_fail()
    {
        $this->assertEmpty(Form::find('contact')->submissions());

        $this
            ->post('/!/forms/contact', [
                'name' => '$',
                'email' => '',
                'message' => '',
            ])
            ->assertSessionHasErrors(['name', 'email', 'message'], null, 'form.contact')
            ->assertLocation('/');

        $this->assertEmpty(Form::find('contact')->submissions());

        $output = $this->tag(<<<'EOT'
{{ form:contact }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="inline-error">{{ error:name }}</p>
{{ /form:contact }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $expected = [
            trans('validation.min.string', ['attribute' => 'Full Name', 'min' => 3]), // 'The Full Name must be at least 3 characters.',
            trans('validation.alpha_num', ['attribute' => 'Full Name']), // 'The Full Name must only contain letters and numbers.',
            trans('validation.required', ['attribute' => 'Email Address']), // 'The Email Address field is required.',
            trans('validation.required', ['attribute' => 'Message']), // 'The Message field is required.',
        ];

        $expectedInline = [
            trans('validation.min.string', ['attribute' => 'Full Name', 'min' => 3]), // 'The Full Name must be at least 3 characters.',
        ];

        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expectedInline, $inlineErrors[1]);
    }

    #[Test]
    public function it_fetches_form_data()
    {
        $form = Statamic::tag('form:contact')->params([
            'js' => 'alpine',
            'files' => true,
            'redirect' => 'http://localhost/',
            'id' => 'my-form',
        ])->fetch();

        $this->assertEquals($form['attrs']['action'], 'http://localhost/!/forms/contact');
        $this->assertEquals($form['attrs']['method'], 'POST');
        $this->assertEquals($form['attrs']['enctype'], 'multipart/form-data');
        $this->assertEquals($form['attrs']['id'], 'my-form');

        $this->assertEquals($form['params']['_redirect'], 'http://localhost/');
        $this->assertArrayHasKey('_token', $form['params']);

        $this->assertIsArray($form['errors']);
        $this->assertIsArray($form['fields']);

        $this->assertEquals($form['honeypot'], 'winnie');
        $this->assertEquals($form['js_driver'], 'alpine');
    }

    #[Test]
    public function it_uploads_assets()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $this->createForm([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => 'One',
                            'instructions' => 'One Instructions',
                            'fields' => [
                                ['handle' => 'alpha', 'field' => ['type' => 'text']],
                                ['handle' => 'bravo', 'field' => ['type' => 'assets', 'container' => 'avatars']],
                            ],
                        ],
                    ],
                ],
            ],
        ], 'survey');

        $this
            ->post('/!/forms/survey', [
                'alpha' => 'test',
                'bravo' => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        Storage::disk('avatars')->assertExists('avatar.jpg');
    }

    #[Test]
    public function it_removes_any_uploaded_assets_when_a_submission_silently_fails()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        Event::listen(function (\Statamic\Events\FormSubmitted $event) {
            return false;
        });

        $this->createForm([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => 'One',
                            'instructions' => 'One Instructions',
                            'fields' => [
                                ['handle' => 'alpha', 'field' => ['type' => 'text']],
                                ['handle' => 'bravo', 'field' => ['type' => 'assets', 'container' => 'avatars']],
                            ],
                        ],
                    ],
                ],
            ],
        ], 'survey');

        $this
            ->post('/!/forms/survey', [
                'alpha' => 'test',
                'bravo' => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        Storage::disk('avatars')->assertMissing('avatar.jpg');
    }

    #[Test]
    public function it_removes_any_uploaded_assets_when_a_listener_throws_a_validation_exception()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        Event::listen(function (\Statamic\Events\FormSubmitted $event) {
            throw ValidationException::withMessages(['custom' => 'This is a custom message']);
        });

        $this->createForm([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => 'One',
                            'instructions' => 'One Instructions',
                            'fields' => [
                                ['handle' => 'alpha', 'field' => ['type' => 'text']],
                                ['handle' => 'bravo', 'field' => ['type' => 'assets', 'container' => 'avatars']],
                            ],
                        ],
                    ],
                ],
            ],
        ], 'survey');

        $this
            ->post('/!/forms/survey', [
                'alpha' => 'test',
                'bravo' => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        Storage::disk('avatars')->assertMissing('avatar.jpg');
    }

    #[Test]
    public function it_renders_file_input_for_assets_and_files_fields_imported_from_a_fieldset()
    {
        $fieldset = (new Fieldset)->setHandle('uploads')->setContents([
            'fields' => [
                ['handle' => 'photo', 'field' => ['type' => 'assets']],
                ['handle' => 'attachment', 'field' => ['type' => 'files']],
            ],
        ]);

        FieldsetRepository::shouldReceive('find')->with('uploads')->andReturn($fieldset);

        $this->createForm([
            'sections' => [
                ['fields' => [['import' => 'uploads']]],
            ],
        ], 'survey');

        $output = $this->tag('{{ form:survey }}{{ form:fields }}{{ field }}{{ /form:fields }}{{ /form:survey }}');

        $this->assertStringContainsString('type="file"', $output);
        $this->assertStringContainsString('name="photo[]"', $output);
        $this->assertStringContainsString('name="attachment[]"', $output);
        $this->assertStringContainsString('multiple', $output);
    }

    #[Test]
    public function it_renders_the_first_pages_sections_by_default()
    {
        $this->createMultiPageForm();

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:survey }}
    {{ sections }}<div class="section">{{ display }}:{{ fields }}{{ handle }},{{ /fields }}</div>{{ /sections }}
{{ /form:survey }}
EOT
        ));

        // Only the first page's section is rendered.
        $this->assertStringContainsString('<div class="section">Section A:name,</div>', $output);
        $this->assertStringNotContainsString('Section B', $output);
        $this->assertStringNotContainsString('email', $output);
    }

    #[Test]
    public function it_renders_a_specific_pages_sections_based_on_the_page_query_param()
    {
        $this->createMultiPageForm();

        request()->merge(['page' => 'page_two']);

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:survey }}
    {{ sections }}<div class="section">{{ display }}:{{ fields }}{{ handle }},{{ /fields }}</div>{{ /sections }}
{{ /form:survey }}
EOT
        ));

        $this->assertStringContainsString('<div class="section">Section B:email,</div>', $output);
        $this->assertStringNotContainsString('Section A', $output);
        $this->assertStringNotContainsString('>name,', $output);
    }

    #[Test]
    public function it_falls_back_to_the_first_page_when_the_page_query_param_is_invalid()
    {
        $this->createMultiPageForm();

        request()->merge(['page' => 'page_nope']);

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:survey }}
    {{ sections }}<div class="section">{{ display }}:{{ fields }}{{ handle }},{{ /fields }}</div>{{ /sections }}
{{ /form:survey }}
EOT
        ));

        $this->assertStringContainsString('<div class="section">Section A:name,</div>', $output);
        $this->assertStringNotContainsString('Section B', $output);
    }

    #[Test]
    public function it_outputs_a_hidden_page_input_for_multi_page_forms()
    {
        $this->createMultiPageForm();

        // The current page defaults to the first.
        $output = $this->tag('{{ form:survey }}{{ /form:survey }}');
        $this->assertStringContainsString('<input type="hidden" name="_page" value="page_one" />', $output);

        // It reflects the page query param.
        request()->merge(['page' => 'page_two']);
        $output = $this->tag('{{ form:survey }}{{ /form:survey }}');
        $this->assertStringContainsString('<input type="hidden" name="_page" value="page_two" />', $output);
    }

    #[Test]
    public function it_does_not_output_a_hidden_page_input_for_single_page_forms()
    {
        // The default contact form (forms-pro disabled) is a single page.
        $output = $this->tag('{{ form:contact }}{{ /form:contact }}');

        $this->assertStringNotContainsString('name="_page"', $output);
    }

    #[Test]
    public function it_populates_fields_from_the_session_partial_submission()
    {
        $this->createMultiPageForm();

        $form = Form::find('survey');
        $form->save();
        $submission = tap($form->makeSubmission()->data(['name' => 'Olaf', 'email' => 'olaf@example.com'])->asPartial())->save();

        session()->put('form.survey.partial_submission', $submission->id());

        // The first page's field is populated with the stored value.
        $pageOne = $this->normalizeHtml($this->tag('{{ form:survey }}{{ fields }}{{ handle }}={{ value }},{{ /fields }}{{ /form:survey }}'));
        $this->assertStringContainsString('name=Olaf,', $pageOne);

        // Navigating to the second page populates its field too.
        request()->merge(['page' => 'page_two']);
        $pageTwo = $this->normalizeHtml($this->tag('{{ form:survey }}{{ fields }}{{ handle }}={{ value }},{{ /fields }}{{ /form:survey }}'));
        $this->assertStringContainsString('email=olaf@example.com,', $pageTwo);

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_does_not_populate_fields_from_a_finalized_submission()
    {
        $this->createMultiPageForm();

        $form = Form::find('survey');
        $form->save();
        $submission = tap($form->makeSubmission()->data(['name' => 'Olaf'])->asPartial())->save();
        $submission->finalize();

        session()->put('form.survey.partial_submission', $submission->id());

        $output = $this->normalizeHtml($this->tag('{{ form:survey }}{{ fields }}{{ handle }}={{ value }},{{ /fields }}{{ /form:survey }}'));

        // The submission is no longer partial, so its values aren't loaded back in.
        $this->assertStringContainsString('name=,', $output);

        $form->submissions()->each->delete();
    }

    #[Test]
    public function it_renders_exceptions_thrown_during_json_requests_as_standard_laravel_errors()
    {
        Event::listen(function (\Statamic\Events\FormSubmitted $event) {
            throw ValidationException::withMessages(['some' => 'error']);
        });

        $response = $this
            ->postJson('/!/forms/contact', [
                'name' => 'Name',
                'email' => 'test@test.com',
                'message' => 'This is a message',
            ]);

        $json = $response->json();

        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertSame($json['errors'], ['some' => ['error']]);
    }

    #[Test]
    public function it_renders_exceptions_thrown_during_xml_http_requests_in_statamic_error_format()
    {
        Event::listen(function (\Statamic\Events\FormSubmitted $event) {
            throw ValidationException::withMessages(['some' => 'error']);
        });

        $response = $this
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->postJson('/!/forms/contact', [
                'name' => 'Name',
                'email' => 'test@test.com',
                'message' => 'This is a message',
            ]);

        $json = $response->json();

        $this->assertArrayHasKey('error', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertSame($json['error'], ['some' => 'error']);
    }

    #[Test]
    public function a_precognitive_success_does_not_persist_a_submission()
    {
        Bus::fake();

        $this->assertEmpty(Form::find('contact')->submissions());

        $this
            ->withPrecognition()
            ->withHeaders(['Precognition-Validate-Only' => 'email'])
            ->postJson('/!/forms/contact', ['email' => 'test@example.com'])
            ->assertNoContent()
            ->assertHeader('Precognition-Success', 'true');

        $this->assertEmpty(Form::find('contact')->submissions());
        Bus::assertNotDispatched(SendEmails::class);
    }

    #[Test]
    public function it_adds_appended_config_fields()
    {
        Form::appendConfigFields('*', 'Fields', [
            'test_config' => ['type' => 'text', 'display' => 'First injected into fields section'],
        ]);

        tap(Form::find('contact')->data(['test_config' => 'This is a test config value']))->save();

        $output = $this->tag('{{ form:contact redirect="/submitted" error_redirect="/errors" class="form" id="form" }}{{ form_config:test_config }}{{ /form:contact }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/forms/contact" class="form" id="form">', $output);
        $this->assertStringContainsString('This is a test config value', $output);
    }

    #[Test]
    public function it_augments_appended_config_fields()
    {
        Form::appendConfigFields('*', 'Fields', [
            'test_config' => ['type' => 'bard', 'display' => 'A Bard field'],
        ]);

        tap(Form::find('contact')->data(
            ['test_config' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Shut up, Malacoustix!']]]]])
        )->save();

        $output = $this->tag('{{ form:contact redirect="/submitted" error_redirect="/errors" class="form" id="form" }}{{ form_config:test_config }}{{ /form:contact }}');

        $this->assertStringContainsString('<p>Shut up, Malacoustix!</p>', $output);
    }
}
