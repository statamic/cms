<?php

namespace Tests\Tags\Form;

use Statamic\Facades\Form;

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
            'handle' => 'fav_animals',
            'field' => [
                'type' => 'checkboxes',
                'display' => 'Fav Animals',
                'options' => [
                    'cat' => 'Cat',
                    'armadillo' => 'Armadillo',
                    'rat' => 'Rat',
                ],
            ],
        ],
    ];

    /** @test */
    public function it_renders_x_data_on_form_tag()
    {
        $output = $this->tag('{{ form:contact js="alpine" }}{{ /form:contact }}');

        $expectedXData = "{'name':null,'email':null,'message':null,'fav_animals':[]}";
        $expected = '<form method="POST" action="http://localhost/!/forms/contact" x-data="'.$expectedXData.'">';

        $this->assertStringContainsString($expected, $output);
    }

    /** @test */
    public function it_renders_x_data_with_old_data_on_form_tag()
    {
        $this
            ->post('/!/forms/contact', [
                'name' => 'Frodo Braggins',
            ])
            ->assertSessionHasErrors(['email', 'message'], null, 'form.contact');

        $output = $this->tag('{{ form:contact js="alpine" }}{{ /form:contact }}');

        $expectedXData = "{'name':'Frodo Braggins','email':null,'message':null,'fav_animals':[]}";
        $expected = '<form method="POST" action="http://localhost/!/forms/contact" x-data="'.$expectedXData.'">';

        $this->assertStringContainsString($expected, $output);
    }

    /** @test */
    public function it_renders_scoped_x_data_on_form_tag()
    {
        $output = $this->tag('{{ form:contact js="alpine:my_form" }}{{ /form:contact }}');

        $expectedXData = "{'my_form':{'name':null,'email':null,'message':null,'fav_animals':[]}}";
        $expected = '<form method="POST" action="http://localhost/!/forms/contact" x-data="'.$expectedXData.'">';

        $this->assertStringContainsString($expected, $output);
    }

    /** @test */
    public function it_renders_scoped_x_data_with_old_data_on_form_tag()
    {
        $this
            ->post('/!/forms/contact', [
                'name' => 'Frodo Braggins',
                'fav_animals' => ['cat'],
            ])
            ->assertSessionHasErrors(['email', 'message'], null, 'form.contact');

        $output = $this->tag('{{ form:contact js="alpine:my_form" }}{{ /form:contact }}');

        $expectedXData = "{'my_form':{'name':'Frodo Braggins','email':null,'message':null,'fav_animals':['cat']}}";
        $expected = '<form method="POST" action="http://localhost/!/forms/contact" x-data="'.$expectedXData.'">';

        $this->assertStringContainsString($expected, $output);
    }

    /** @test */
    public function it_renders_show_field_js()
    {
        $outputWithJsDisabled = $this->tag('{{ form:contact }}{{ /form:contact }}');

        $output = $this->tag(<<<'EOT'
{{ form:contact js="alpine" }}
    <template x-if="{{ show_field:name }}"></template>
    <template x-if="{{ show_field:message }}"></template>
    {{ fields }}
        <template x-if="{{ show_field }}"></template>
    {{ /fields }}
{{ /form:contact }}
EOT
        );

        preg_match_all('/<template x-if="(.+)"><\/template>/U', $output, $js);

        $expected = [
            'Statamic.$conditions.showField([], $data)',
            'Statamic.$conditions.showField({\'if\':{\'email\':\'not empty\'}}, $data)',
            'Statamic.$conditions.showField([], $data)',
            'Statamic.$conditions.showField([], $data)',
            'Statamic.$conditions.showField({\'if\':{\'email\':\'not empty\'}}, $data)',
            'Statamic.$conditions.showField([], $data)',
        ];

        $this->assertStringNotContainsString('Statamic.$conditions', $outputWithJsDisabled);
        $this->assertEquals($expected, $js[1]);
    }

    /** @test */
    public function it_renders_scoped_show_field_js()
    {
        $outputWithJsDisabled = $this->tag('{{ form:contact }}{{ /form:contact }}');

        $output = $this->tag(<<<'EOT'
{{ form:contact js="alpine:my_form" }}
    <template x-if="{{ show_field:name }}"></template>
    <template x-if="{{ show_field:message }}"></template>
    {{ fields }}
        <template x-if="{{ show_field }}"></template>
    {{ /fields }}
{{ /form:contact }}
EOT
        );

        preg_match_all('/<template x-if="(.+)"><\/template>/U', $output, $js);

        $expected = [
            'Statamic.$conditions.showField([], $data.my_form)',
            'Statamic.$conditions.showField({\'if\':{\'email\':\'not empty\'}}, $data.my_form)',
            'Statamic.$conditions.showField([], $data.my_form)',
            'Statamic.$conditions.showField([], $data.my_form)',
            'Statamic.$conditions.showField({\'if\':{\'email\':\'not empty\'}}, $data.my_form)',
            'Statamic.$conditions.showField([], $data.my_form)',
        ];

        $this->assertStringNotContainsString('Statamic.$conditions', $outputWithJsDisabled);
        $this->assertEquals($expected, $js[1]);
    }

    /** @test */
    public function it_dynamically_renders_text_field_x_model()
    {
        $textConfig = [
            'handle' => 'name',
            'field' => [
                'type' => 'text',
            ],
        ];

        $this->assertFieldRendersHtml(['<input type="text" name="name" value="" x-model="name">'], $textConfig, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml(['<input type="text" name="name" value="" x-model="my_form.name">'], $textConfig, [], ['js' => 'alpine:my_form']);
    }

    /** @test */
    public function it_dynamically_renders_textarea_field_x_model()
    {
        $textConfig = [
            'handle' => 'comment',
            'field' => [
                'type' => 'textarea',
            ],
        ];

        $this->assertFieldRendersHtml(['<textarea name="comment" rows="5" x-model="comment"></textarea>'], $textConfig, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml(['<textarea name="comment" rows="5" x-model="my_form.comment"></textarea>'], $textConfig, [], ['js' => 'alpine:my_form']);
    }

    /** @test */
    public function it_dynamically_renders_checkboxes_field_x_model()
    {
        $textConfig = [
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

        $this->assertFieldRendersHtml(['<input type="checkbox" name="fav_animals[]" value="cat" x-model="fav_animals">'], $textConfig, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml(['<input type="checkbox" name="fav_animals[]" value="rat" x-model="fav_animals">'], $textConfig, [], ['js' => 'alpine']);
        $this->assertFieldRendersHtml(['<input type="checkbox" name="fav_animals[]" value="armadillo" x-model="fav_animals">'], $textConfig, [], ['js' => 'alpine']);

        $this->assertFieldRendersHtml(['<input type="checkbox" name="fav_animals[]" value="cat" x-model="my_form.fav_animals">'], $textConfig, [], ['js' => 'alpine:my_form']);
        $this->assertFieldRendersHtml(['<input type="checkbox" name="fav_animals[]" value="rat" x-model="my_form.fav_animals">'], $textConfig, [], ['js' => 'alpine:my_form']);
        $this->assertFieldRendersHtml(['<input type="checkbox" name="fav_animals[]" value="armadillo" x-model="my_form.fav_animals">'], $textConfig, [], ['js' => 'alpine:my_form']);
    }
}
