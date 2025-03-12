<?php

namespace Tests\Tags\Form;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Statamic;

class FormCreateAlpineTest extends FormTestCase
{
    protected $defaultFields = [
        [
            'handle' => 'name',
            'field' => [
                'type' => 'text',
                'display' => 'Full Name',
                'validate' => 'min:3|alpha_num',
            ],
        ],
        [
            'handle' => 'email',
            'field' => [
                'type' => 'text',
                'input_type' => 'email',
                'display' => 'Email Address',
                'validate' => 'required|email',
            ],
        ],
        [
            'handle' => 'message',
            'field' => [
                'type' => 'textarea',
                'display' => 'Message',
                'validate' => 'required',
                'if' => [
                    'email' => 'not empty',
                ],
            ],
        ],
        [
            'handle' => 'likes_animals',
            'field' => [
                'type' => 'toggle',
                'display' => 'Likes Animals',
            ],
        ],
        [
            'handle' => 'my_favourites',
            'field' => [
                'type' => 'group',
                'display' => 'Group One',
                'if' => [
                    'name' => 'not empty',
                ],
                'fields' => [
                    [
                        'handle' => 'favourite_animals',
                        'field' => [
                            'type' => 'checkboxes',
                            'display' => 'Favourite Animals',
                            'options' => [
                                'cat' => 'Cat',
                                'armadillo' => 'Armadillo',
                                'rat' => 'Rat',
                            ],
                            'if' => [
                                '$root.likes_animals' => 'is true',
                            ],
                        ],
                    ],
                    [
                        'handle' => 'non_favourite_animals',
                        'field' => [
                            'type' => 'checkboxes',
                            'display' => 'Non-Favourite Animals',
                            'options' => [
                                'cat' => 'Cat',
                                'armadillo' => 'Armadillo',
                                'rat' => 'Rat',
                            ],
                            'if' => [
                                'favourite_animals' => 'not empty',
                            ],
                        ],
                    ],
                    [
                        'handle' => 'favourite_colour',
                        'field' => [
                            'type' => 'radio',
                            'display' => 'Favourite Colour',
                            'options' => [
                                'red' => 'Red',
                                'blue' => 'Blue',
                            ],
                        ],
                    ],
                    [
                        'handle' => 'favourite_subject',
                        'field' => [
                            'type' => 'select',
                            'display' => 'Favourite Subject',
                            'options' => [
                                'math' => 'Math',
                                'english' => 'English',
                            ],
                            'if' => [
                                '$parent.message' => 'not empty',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    #[Test]
    public function it_shows_js_driver_in_form_data()
    {
        $this->assertStringContainsString(
            '<span></span>',
            $this->tag('{{ form:contact }}<span>{{ js_driver }}</span>{{ /form:contact }}')
        );

        $this->assertStringContainsString(
            '<span>alpine</span>',
            $this->tag('{{ form:contact js="alpine" }}<span>{{ js_driver }}</span>{{ /form:contact }}')
        );
    }

    #[Test]
    public function it_renders_x_data_on_form_tag()
    {
        $output = $this->tag('{{ form:contact js="alpine" }}{{ /form:contact }}');

        $expectedXData = $this->jsonEncode([
            'name' => null,
            'email' => null,
            'message' => null,
            'likes_animals' => false,
            'my_favourites' => [
                'favourite_animals' => [],
                'non_favourite_animals' => [],
                'favourite_colour' => null,
                'favourite_subject' => null,
            ],
            'winnie' => null,
        ]);

        $expected = '<form method="POST" action="http://localhost/!/forms/contact" x-data="'.$expectedXData.'">';

        $this->assertStringContainsString($expected, $output);
    }

    #[Test]
    public function it_renders_x_data_with_old_data_on_form_tag()
    {
        $this
            ->post('/!/forms/contact', [
                'name' => 'Frodo Braggins',
            ])
            ->assertSessionHasErrors(['email', 'message'], null, 'form.contact');

        $output = $this->tag('{{ form:contact js="alpine" }}{{ /form:contact }}');

        $expectedXData = $this->jsonEncode([
            'name' => 'Frodo Braggins',
            'email' => null,
            'message' => null,
            'likes_animals' => false,
            'my_favourites' => [
                'favourite_animals' => [],
                'non_favourite_animals' => [],
                'favourite_colour' => null,
                'favourite_subject' => null,
            ],
            'winnie' => null,
        ]);

        $expected = '<form method="POST" action="http://localhost/!/forms/contact" x-data="'.$expectedXData.'">';

        $this->assertStringContainsString($expected, $output);
    }

    #[Test]
    public function it_renders_scoped_x_data_on_form_tag()
    {
        $output = $this->tag('{{ form:contact js="alpine:my_form" }}{{ /form:contact }}');

        $expectedXData = $this->jsonEncode([
            'my_form' => [
                'name' => null,
                'email' => null,
                'message' => null,
                'likes_animals' => false,
                'my_favourites' => [
                    'favourite_animals' => [],
                    'non_favourite_animals' => [],
                    'favourite_colour' => null,
                    'favourite_subject' => null,
                ],
                'winnie' => null,
            ],
        ]);

        $expected = '<form method="POST" action="http://localhost/!/forms/contact" x-data="'.$expectedXData.'">';

        $this->assertStringContainsString($expected, $output);
    }

    #[Test]
    public function it_renders_scoped_x_data_with_old_data_on_form_tag()
    {
        $this
            ->post('/!/forms/contact', [
                'name' => 'Frodo Braggins',
                'fav_animals' => ['cat'],
            ])
            ->assertSessionHasErrors(['email', 'message'], null, 'form.contact');

        $output = $this->tag('{{ form:contact js="alpine:my_form" }}{{ /form:contact }}');

        $expectedXData = $this->jsonEncode([
            'my_form' => [
                'name' => 'Frodo Braggins',
                'email' => null,
                'message' => null,
                'likes_animals' => false,
                'my_favourites' => [
                    'favourite_animals' => [],
                    'non_favourite_animals' => [],
                    'favourite_colour' => null,
                    'favourite_subject' => null,
                ],
                'winnie' => null,
            ],
        ]);

        $expected = '<form method="POST" action="http://localhost/!/forms/contact" x-data="'.$expectedXData.'">';

        $this->assertStringContainsString($expected, $output);
    }

    #[Test]
    public function it_renders_proper_x_data_for_multiple_select_field()
    {
        $config = [
            'handle' => 'favourite_animals',
            'field' => [
                'type' => 'select',
                'multiple' => true,
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => 'Rat',
                ],
            ],
        ];

        $expected = 'x-data="'.$this->jsonEncode(['favourite_animals' => [], 'winnie' => null]).'"';

        $this->assertFieldRendersHtml($expected, $config, [], ['js' => 'alpine']);
    }

    #[Test]
    public function it_renders_proper_x_data_for_multiple_assets_field()
    {
        $config = [
            'handle' => 'selfies',
            'field' => [
                'type' => 'assets',
                'display' => 'Selfies',
                'max_files' => 3,
            ],
        ];

        $expected = 'x-data="'.$this->jsonEncode(['selfies' => [], 'winnie' => null]).'"';

        $this->assertFieldRendersHtml($expected, $config, [], ['js' => 'alpine']);
    }

    #[Test]
    public function it_renders_show_field_js_recursively()
    {
        $outputWithJsDisabled = $this->tag('{{ form:contact }}{{ /form:contact }}');

        $output = $this->tag(<<<'EOT'
{{ form:contact js="alpine" }}
    {{ form:fields }}
        <template x-if="{{ show_field }}">{{ field }}</template>
    {{ /form:fields }}
{{ /form:contact }}
EOT
        );

        preg_match_all('/<template x-if="(.+)">/U', $output, $js);

        $expected = [
            'Statamic.$conditions.showField([], $data, \'name\')',
            'Statamic.$conditions.showField([], $data, \'email\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['email' => 'not empty']]).', $data, \'message\')',
            'Statamic.$conditions.showField([], $data, \'likes_animals\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['name' => 'not empty']]).', $data, \'my_favourites\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['$root.likes_animals' => 'is true']]).', $data, \'my_favourites.favourite_animals\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['favourite_animals' => 'not empty']]).', $data, \'my_favourites.non_favourite_animals\')',
            'Statamic.$conditions.showField([], $data, \'my_favourites.favourite_colour\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['$parent.message' => 'not empty']]).', $data, \'my_favourites.favourite_subject\')',
        ];

        $this->assertStringNotContainsString('Statamic.$conditions', $outputWithJsDisabled);
        $this->assertEquals($expected, $js[1]);
    }

    #[Test]
    public function it_renders_scoped_show_field_js_recursively()
    {
        $outputWithJsDisabled = $this->tag('{{ form:contact }}{{ /form:contact }}');

        $output = $this->tag(<<<'EOT'
{{ form:contact js="alpine:my_form" }}
    {{ form:fields }}
        <template x-if="{{ show_field }}">{{ field }}</template>
    {{ /form:fields }}
{{ /form:contact }}
EOT
        );

        preg_match_all('/<template x-if="(.+)">/U', $output, $js);

        $expected = [
            'Statamic.$conditions.showField([], $data.my_form, \'name\')',
            'Statamic.$conditions.showField([], $data.my_form, \'email\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['email' => 'not empty']]).', $data.my_form, \'message\')',
            'Statamic.$conditions.showField([], $data.my_form, \'likes_animals\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['name' => 'not empty']]).', $data.my_form, \'my_favourites\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['$root.likes_animals' => 'is true']]).', $data.my_form, \'my_favourites.favourite_animals\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['favourite_animals' => 'not empty']]).', $data.my_form, \'my_favourites.non_favourite_animals\')',
            'Statamic.$conditions.showField([], $data.my_form, \'my_favourites.favourite_colour\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['$parent.message' => 'not empty']]).', $data.my_form, \'my_favourites.favourite_subject\')',
        ];

        $this->assertStringNotContainsString('Statamic.$conditions', $outputWithJsDisabled);
        $this->assertEquals($expected, $js[1]);
    }

    #[Test]
    public function it_renders_show_field_js_inside_legacy_fields_array()
    {
        $outputWithJsDisabled = $this->tag('{{ form:contact }}{{ /form:contact }}');

        $output = $this->tag(<<<'EOT'
{{ form:contact js="alpine" }}
    {{ fields }}
        <template x-if="{{ show_field }}">{{ field }}</template>
    {{ /fields }}
{{ /form:contact }}
EOT
        );

        preg_match_all('/<template x-if="(.+)">/U', $output, $js);

        $expected = [
            'Statamic.$conditions.showField([], $data, \'name\')',
            'Statamic.$conditions.showField([], $data, \'email\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['email' => 'not empty']]).', $data, \'message\')',
            'Statamic.$conditions.showField([], $data, \'likes_animals\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['name' => 'not empty']]).', $data, \'my_favourites\')',
        ];

        $this->assertStringNotContainsString('Statamic.$conditions', $outputWithJsDisabled);
        $this->assertEquals($expected, $js[1]);
    }

    #[Test]
    public function it_renders_scoped_show_field_js_inside_legacy_fields_array()
    {
        $outputWithJsDisabled = $this->tag('{{ form:contact }}{{ /form:contact }}');

        $output = $this->tag(<<<'EOT'
{{ form:contact js="alpine:my_form" }}
    {{ fields }}
        <template x-if="{{ show_field }}">{{ field }}</template>
    {{ /fields }}
{{ /form:contact }}
EOT
        );

        preg_match_all('/<template x-if="(.+)">/U', $output, $js);

        $expected = [
            'Statamic.$conditions.showField([], $data.my_form, \'name\')',
            'Statamic.$conditions.showField([], $data.my_form, \'email\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['email' => 'not empty']]).', $data.my_form, \'message\')',
            'Statamic.$conditions.showField([], $data.my_form, \'likes_animals\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['name' => 'not empty']]).', $data.my_form, \'my_favourites\')',
        ];

        $this->assertStringNotContainsString('Statamic.$conditions', $outputWithJsDisabled);
        $this->assertEquals($expected, $js[1]);
    }

    #[Test]
    public function it_renders_top_level_show_field_js()
    {
        $outputWithJsDisabled = $this->tag('{{ form:contact }}{{ /form:contact }}');

        $output = $this->tag(<<<'EOT'
{{ form:contact js="alpine" }}
    <template x-if="{{ show_field:name }}"></template>
    <template x-if="{{ show_field.message }}"></template>
    <template x-if="{{ show_field['my_favourites'] }}"></template>
    <template x-if="{{ show_field['my_favourites.favourite_animals'] }}"></template>
    <template x-if="{{ show_field['my_favourites.non_favourite_animals'] }}"></template>
    <template x-if="{{ show_field['my_favourites.favourite_colour'] }}"></template>
{{ /form:contact }}
EOT
        );

        preg_match_all('/<template x-if="(.+)"><\/template>/U', $output, $js);

        $expected = [
            'Statamic.$conditions.showField([], $data, \'name\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['email' => 'not empty']]).', $data, \'message\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['name' => 'not empty']]).', $data, \'my_favourites\')', // TODO
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['$root.likes_animals' => 'is true']]).', $data, \'my_favourites.favourite_animals\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['favourite_animals' => 'not empty']]).', $data, \'my_favourites.non_favourite_animals\')',
            'Statamic.$conditions.showField([], $data, \'my_favourites.favourite_colour\')',
        ];

        $this->assertStringNotContainsString('Statamic.$conditions', $outputWithJsDisabled);
        $this->assertSame($expected, $js[1]);
    }

    #[Test]
    public function it_renders_scoped_top_level_show_field_js()
    {
        $outputWithJsDisabled = $this->tag('{{ form:contact }}{{ /form:contact }}');

        $output = $this->tag(<<<'EOT'
{{ form:contact js="alpine:my_form" }}
    <template x-if="{{ show_field:name }}"></template>
    <template x-if="{{ show_field.message }}"></template>
    <template x-if="{{ show_field['my_favourites'] }}"></template>
    <template x-if="{{ show_field['my_favourites.favourite_animals'] }}"></template>
    <template x-if="{{ show_field['my_favourites.non_favourite_animals'] }}"></template>
    <template x-if="{{ show_field['my_favourites.favourite_colour'] }}"></template>
{{ /form:contact }}
EOT
        );

        preg_match_all('/<template x-if="(.+)"><\/template>/U', $output, $js);

        $expected = [
            'Statamic.$conditions.showField([], $data.my_form, \'name\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['email' => 'not empty']]).', $data.my_form, \'message\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['name' => 'not empty']]).', $data.my_form, \'my_favourites\')', // TODO
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['$root.likes_animals' => 'is true']]).', $data.my_form, \'my_favourites.favourite_animals\')',
            'Statamic.$conditions.showField('.$this->jsonEncode(['if' => ['favourite_animals' => 'not empty']]).', $data.my_form, \'my_favourites.non_favourite_animals\')',
            'Statamic.$conditions.showField([], $data.my_form, \'my_favourites.favourite_colour\')',
        ];

        $this->assertStringNotContainsString('Statamic.$conditions', $outputWithJsDisabled);
        $this->assertSame($expected, $js[1]);
    }

    #[Test]
    public function it_dynamically_renders_text_field_x_model()
    {
        $config = [
            'handle' => 'name',
            'field' => [
                'type' => 'text',
            ],
        ];

        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-name-field" type="text" name="name" value="" x-model="name">'], $config, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-name-field" type="text" name="name" value="" x-model="my_form.name">'], $config, [], ['js' => 'alpine:my_form']);
    }

    #[Test]
    public function it_dynamically_renders_textarea_field_x_model()
    {
        $config = [
            'handle' => 'comment',
            'field' => [
                'type' => 'textarea',
            ],
        ];

        $this->assertFieldRendersHtml(['<textarea id="[[form-handle]]-form-comment-field" name="comment" rows="5" x-model="comment"></textarea>'], $config, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml(['<textarea id="[[form-handle]]-form-comment-field" name="comment" rows="5" x-model="my_form.comment"></textarea>'], $config, [], ['js' => 'alpine:my_form']);
    }

    #[Test]
    public function it_dynamically_renders_checkboxes_field_x_model()
    {
        $config = [
            'handle' => 'fav_animals',
            'field' => [
                'type' => 'checkboxes',
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => 'Rat',
                ],
            ],
        ];

        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animals-field-cat-option" type="checkbox" name="fav_animals[]" value="cat" x-model="fav_animals">'], $config, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animals-field-rat-option" type="checkbox" name="fav_animals[]" value="rat" x-model="fav_animals">'], $config, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animals-field-armadillo-option" type="checkbox" name="fav_animals[]" value="armadillo" x-model="fav_animals">'], $config, [], ['js' => 'alpine']);

        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animals-field-cat-option" type="checkbox" name="fav_animals[]" value="cat" x-model="my_form.fav_animals">'], $config, [], ['js' => 'alpine:my_form']);
        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animals-field-rat-option" type="checkbox" name="fav_animals[]" value="rat" x-model="my_form.fav_animals">'], $config, [], ['js' => 'alpine:my_form']);
        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animals-field-armadillo-option" type="checkbox" name="fav_animals[]" value="armadillo" x-model="my_form.fav_animals">'], $config, [], ['js' => 'alpine:my_form']);
    }

    #[Test]
    public function it_dynamically_renders_radio_field_x_model()
    {
        $config = [
            'handle' => 'fav_animal',
            'field' => [
                'type' => 'radio',
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => 'Rat',
                ],
            ],
        ];

        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animal-field-cat-option" type="radio" name="fav_animal" value="cat" x-model="fav_animal">'], $config, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animal-field-rat-option" type="radio" name="fav_animal" value="rat" x-model="fav_animal">'], $config, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animal-field-armadillo-option" type="radio" name="fav_animal" value="armadillo" x-model="fav_animal">'], $config, [], ['js' => 'alpine']);

        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animal-field-cat-option" type="radio" name="fav_animal" value="cat" x-model="my_form.fav_animal">'], $config, [], ['js' => 'alpine:my_form']);
        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animal-field-rat-option" type="radio" name="fav_animal" value="rat" x-model="my_form.fav_animal">'], $config, [], ['js' => 'alpine:my_form']);
        $this->assertFieldRendersHtml(['<input id="[[form-handle]]-form-fav_animal-field-armadillo-option" type="radio" name="fav_animal" value="armadillo" x-model="my_form.fav_animal">'], $config, [], ['js' => 'alpine:my_form']);
    }

    #[Test]
    public function it_dynamically_renders_select_field_x_model()
    {
        $config = [
            'handle' => 'favourite_animal',
            'field' => [
                'type' => 'select',
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => 'Rat',
                ],
            ],
        ];

        $expected = [
            '<select id="[[form-handle]]-form-favourite_animal-field" name="favourite_animal" x-model="favourite_animal">',
            '<option value>Please select...</option>',
            '<option value="cat">Cat</option>',
            '<option value="armadillo">Armadillo</option>',
            '<option value="rat">Rat</option>',
            '</select>',
        ];

        $this->assertFieldRendersHtml($expected, $config, [], ['js' => 'alpine']);

        $expectedScoped = [
            '<select id="[[form-handle]]-form-favourite_animal-field" name="favourite_animal" x-model="my_form.favourite_animal">',
            '<option value>Please select...</option>',
            '<option value="cat">Cat</option>',
            '<option value="armadillo">Armadillo</option>',
            '<option value="rat">Rat</option>',
            '</select>',
        ];

        $this->assertFieldRendersHtml($expectedScoped, $config, [], ['js' => 'alpine:my_form']);
    }

    #[Test]
    public function it_dynamically_renders_asset_field_x_model()
    {
        $config = [
            'handle' => 'cat_selfie',
            'field' => [
                'type' => 'assets',
                'display' => 'Cat Selfie',
                'max_files' => 1,
            ],
        ];

        $this->assertFieldRendersHtml('<input id="[[form-handle]]-form-cat_selfie-field" type="file" name="cat_selfie" x-model="cat_selfie">', $config, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml('<input id="[[form-handle]]-form-cat_selfie-field" type="file" name="cat_selfie" x-model="my_form.cat_selfie">', $config, [], ['js' => 'alpine:my_form']);
    }

    #[Test]
    public function it_dynamically_renders_group_field_without_x_model_but_x_models_fields_within()
    {
        $config = [
            'handle' => 'address',
            'field' => [
                'type' => 'group',
                'fields' => [
                    ['handle' => 'street', 'field' => ['type' => 'text']],
                    ['handle' => 'country', 'field' => ['type' => 'text']],
                ],
            ],
        ];

        $this->assertFieldRendersHtml(collect([
            '<div>',
            '<input id="[[form-handle]]-form-address-street-field" type="text" name="address[street]" value="" x-model="address.street">',
            '<input id="[[form-handle]]-form-address-country-field" type="text" name="address[country]" value="" x-model="address.country">',
            '</div>',
        ])->implode(''), $config, [], ['js' => 'alpine']);

        $this->assertFieldRendersHtml(collect([
            '<div>',
            '<input id="[[form-handle]]-form-address-street-field" type="text" name="address[street]" value="" x-model="my_form.address.street">',
            '<input id="[[form-handle]]-form-address-country-field" type="text" name="address[country]" value="" x-model="my_form.address.country">',
            '</div>',
        ])->implode(''), $config, [], ['js' => 'alpine:my_form']);
    }

    #[Test]
    public function it_dynamically_renders_group_field_without_x_model_but_x_models_deeply_nested_fields_within()
    {
        $config = [
            'handle' => 'group_one',
            'field' => [
                'type' => 'group',
                'fields' => [
                    [
                        'handle' => 'nested_field',
                        'field' => ['type' => 'text'],
                    ],
                    [
                        'handle' => 'group_two',
                        'field' => [
                            'type' => 'group',
                            'fields' => [
                                [
                                    'handle' => 'deeply_nested_field',
                                    'field' => ['type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertFieldRendersHtml(collect([
            '<div>',
            '<input id="[[form-handle]]-form-group_one-nested_field-field" type="text" name="group_one[nested_field]" value="" x-model="group_one.nested_field">',
            '<div>',
            '<input id="[[form-handle]]-form-group_one-group_two-deeply_nested_field-field" type="text" name="group_one[group_two][deeply_nested_field]" value="" x-model="group_one.group_two.deeply_nested_field">',
            '</div>',
            '</div>',
        ])->implode(''), $config, [], ['js' => 'alpine']);

        $this->assertFieldRendersHtml(collect([
            '<div>',
            '<input id="[[form-handle]]-form-group_one-nested_field-field" type="text" name="group_one[nested_field]" value="" x-model="group_one.nested_field">',
            '<div>',
            '<input id="[[form-handle]]-form-group_one-group_two-deeply_nested_field-field" type="text" name="group_one[group_two][deeply_nested_field]" value="" x-model="group_one.group_two.deeply_nested_field">',
            '</div>',
            '</div>',
        ])->implode(''), $config, [], ['js' => 'alpine']);
    }

    #[Test]
    public function it_dynamically_renders_field_with_fallback_to_default_partial_x_model()
    {
        $config = [
            'handle' => 'custom',
            'field' => [
                'type' => 'markdown', // 'markdown' doesn't have a template, so it should fall back to default.antlers.html
            ],
        ];

        $this->assertFieldRendersHtml('<input id="[[form-handle]]-form-custom-field" type="text" name="custom" value="" x-model="custom">', $config, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml('<input id="[[form-handle]]-form-custom-field" type="text" name="custom" value="" x-model="my_form.custom">', $config, [], ['js' => 'alpine:my_form']);
    }

    #[Test]
    public function it_merges_any_x_data_passed_to_the_tag()
    {
        $output = $this->tag('{{ form:contact js="alpine:my_form" \x-data=\'{"extra":"yes"}\' }}{{ /form:contact }}');

        $expectedXData = $this->jsonEncode([
            'my_form' => [
                'name' => null,
                'email' => null,
                'message' => null,
                'likes_animals' => false,
                'my_favourites' => [
                    'favourite_animals' => [],
                    'non_favourite_animals' => [],
                    'favourite_colour' => null,
                    'favourite_subject' => null,
                ],
                'winnie' => null,
                'extra' => 'yes',
            ],
        ]);

        $expected = '<form method="POST" action="http://localhost/!/forms/contact" x-data="'.$expectedXData.'">';

        $this->assertStringContainsString($expected, $output);

        $params = ['xdata' => ['extra' => 'no']];

        $output = $this->tag('{{ form:contact js="alpine:my_form" :x-data="xdata" }}{{ /form:contact }}', $params);

        $expectedXData = $this->jsonEncode([
            'my_form' => [
                'name' => null,
                'email' => null,
                'message' => null,
                'likes_animals' => false,
                'my_favourites' => [
                    'favourite_animals' => [],
                    'non_favourite_animals' => [],
                    'favourite_colour' => null,
                    'favourite_subject' => null,
                ],
                'winnie' => null,
                'extra' => 'no',
            ],
        ]);

        $expected = '<form method="POST" action="http://localhost/!/forms/contact" x-data="'.$expectedXData.'">';

        $this->assertStringContainsString($expected, $output);
    }

    private function jsonEncode($data)
    {
        return Statamic::modify($data)->toJson()->entities();
    }
}
