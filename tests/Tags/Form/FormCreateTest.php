<?php

namespace Tests\Tags\Form;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Form;
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
    public function it_dynamically_renders_asset_field()
    {
        $this->assertFieldRendersHtml([
            '<input id="[[form-handle]]-form-cat-selfie-field" type="file" name="cat_selfie">',
        ], [
            'handle' => 'cat_selfie',
            'field' => [
                'type' => 'assets',
                'display' => 'Cat Selfie',
                'max_files' => 1,
            ],
        ]);
    }

    #[Test]
    public function it_dynamically_renders_multiple_assets_field()
    {
        $this->assertFieldRendersHtml([
            '<input id="[[form-handle]]-form-cat-selfies-field" type="file" name="cat_selfies[]" multiple>',
        ], [
            'handle' => 'cat_selfies',
            'field' => [
                'type' => 'assets',
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
}
