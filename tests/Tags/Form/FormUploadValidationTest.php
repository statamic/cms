<?php

namespace Tests\Tags\Form;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;

class FormUploadValidationTest extends FormTestCase
{
    #[Test]
    public function it_enforces_default_allowed_extensions_on_a_files_field()
    {
        Storage::fake('local');

        $this->createForm([
            'fields' => [
                ['handle' => 'document', 'field' => ['type' => 'files']],
            ],
        ], 'survey');

        $this
            ->post('/!/forms/survey', [
                'document' => UploadedFile::fake()->create('evil.php', 10),
            ])
            ->assertSessionHasErrors('document.0', null, 'form.survey');
    }

    #[Test]
    public function it_allows_uploads_permitted_by_the_default_allowed_extensions()
    {
        Storage::fake('local');

        $this->createForm([
            'fields' => [
                ['handle' => 'document', 'field' => ['type' => 'files']],
            ],
        ], 'survey');

        $this
            ->post('/!/forms/survey', [
                'document' => UploadedFile::fake()->create('notes.txt', 10),
            ])
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function it_enforces_allowed_extensions_on_a_files_field()
    {
        Storage::fake('local');

        $this->createForm([
            'fields' => [
                ['handle' => 'document', 'field' => ['type' => 'files', 'allowed_extensions' => ['pdf']]],
            ],
        ], 'survey');

        $this
            ->post('/!/forms/survey', [
                'document' => UploadedFile::fake()->create('notes.txt', 10),
            ])
            ->assertSessionHasErrors('document.0', null, 'form.survey');
    }

    #[Test]
    public function it_allows_uploads_permitted_by_the_allowed_extensions()
    {
        Storage::fake('local');

        $this->createForm([
            'fields' => [
                ['handle' => 'document', 'field' => ['type' => 'files', 'allowed_extensions' => ['pdf']]],
            ],
        ], 'survey');

        $this
            ->post('/!/forms/survey', [
                'document' => UploadedFile::fake()->create('notes.pdf', 10),
            ])
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function it_enforces_asset_container_validation_rules_on_an_assets_field()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->validationRules(['extensions:jpg,png'])->save();

        $this->createForm([
            'fields' => [
                ['handle' => 'bravo', 'field' => ['type' => 'assets', 'container' => 'avatars', 'max_files' => 1]],
            ],
        ], 'survey');

        $this
            ->post('/!/forms/survey', [
                'bravo' => UploadedFile::fake()->create('script.js', 10),
            ])
            ->assertSessionHasErrors('bravo.0', null, 'form.survey');
    }

    #[Test]
    public function it_allows_uploads_permitted_by_the_asset_container_validation_rules()
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->validationRules(['extensions:jpg,png'])->save();

        $this->createForm([
            'fields' => [
                ['handle' => 'bravo', 'field' => ['type' => 'assets', 'container' => 'avatars', 'max_files' => 1]],
            ],
        ], 'survey');

        $this
            ->post('/!/forms/survey', [
                'bravo' => UploadedFile::fake()->image('avatar.jpg'),
            ])
            ->assertSessionHasNoErrors();
    }
}
