<?php

namespace Tests\Tags\Form;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;

class FormGroupChildrenTest extends FormTestCase
{
    #[Test]
    public function it_renders_a_group_with_form_fieldtype_children()
    {
        $this->createGroupForm();

        $output = $this->normalizeHtml($this->tag('{{ form:grouped }}{{ form:fields }}{{ field }}{{ /form:fields }}{{ /form:grouped }}'));

        $this->assertStringContainsString('name="address[city]"', $output);
        $this->assertMatchesRegularExpression('/<input[^>]+type="email"[^>]+name="address\[contact\]"/', $output);
        $this->assertMatchesRegularExpression('/<input[^>]+type="tel"[^>]+name="address\[phone\]"[^>]+autocomplete="tel"/', $output);
    }

    #[Test]
    public function it_renders_a_group_with_form_fieldtype_children_in_blade()
    {
        $this->createGroupForm();

        $output = $this->normalizeHtml($this->blade(<<<'EOT'
<s:form:grouped><s:form:fields>{!! $field['field'] !!}</s:form:fields></s:form:grouped>
EOT));

        $this->assertStringContainsString('name="address[city]"', $output);
    }

    #[Test]
    public function it_submits_a_group_with_form_fieldtype_children()
    {
        $this->createGroupForm();

        $this
            ->post('/!/forms/grouped', ['address' => ['city' => 'Paris', 'contact' => 'bob@example.com']])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $this->assertEquals(
            ['city' => 'Paris', 'contact' => 'bob@example.com'],
            Form::find('grouped')->submissions()->first()->get('address')
        );
    }

    #[Test]
    public function it_validates_form_fieldtype_children_of_a_group()
    {
        $this->createGroupForm();

        $this
            ->post('/!/forms/grouped', ['address' => ['city' => '', 'contact' => 'not-an-email']])
            ->assertSessionHasErrors(['address.city', 'address.contact'], null, 'form.grouped');

        $this->assertCount(0, Form::find('grouped')->submissions());
    }

    #[Test]
    public function it_doesnt_render_or_upload_a_legacy_upload_field_inside_a_group()
    {
        Storage::fake('local');

        $this->createForm([
            'fields' => [
                ['handle' => 'details', 'field' => ['type' => 'group', 'fields' => [
                    ['handle' => 'name', 'field' => ['type' => 'text']],
                    ['handle' => 'resume', 'field' => ['type' => 'files', 'validate' => ['required']]],
                ]]],
            ],
        ], 'legacy');

        $output = $this->normalizeHtml($this->tag('{{ form:legacy files="true" }}{{ form:fields }}{{ field }}{{ /form:fields }}{{ /form:legacy }}'));

        $this->assertStringContainsString('name="details[name]"', $output);
        $this->assertStringNotContainsString('type="file"', $output);
        $this->assertStringNotContainsString('details[resume]', $output);

        $this
            ->post('/!/forms/legacy', [
                'details' => ['name' => 'Bob', 'resume' => UploadedFile::fake()->create('resume.pdf', 10)],
            ])
            ->assertSessionHasNoErrors();

        $this->assertEquals(['name' => 'Bob'], Form::find('legacy')->submissions()->first()->get('details'));
        $this->assertEmpty(Storage::disk('local')->allFiles());

        Form::find('legacy')->submissions()->each->delete();
    }

    protected function clearSubmissions()
    {
        parent::clearSubmissions();

        Form::find('grouped')?->submissions()->each->delete();
    }

    private function createGroupForm(): void
    {
        $this->createForm([
            'fields' => [
                ['handle' => 'address', 'field' => [
                    'type' => 'group',
                    'display' => 'Address',
                    'fields' => [
                        ['handle' => 'city', 'field' => ['type' => 'short_answer', 'display' => 'City', 'validate' => ['required']]],
                        ['handle' => 'contact', 'field' => ['type' => 'email', 'display' => 'Contact']],
                        ['handle' => 'phone', 'field' => ['type' => 'phone', 'display' => 'Phone']],
                    ],
                ]],
            ],
        ], 'grouped');
    }
}
