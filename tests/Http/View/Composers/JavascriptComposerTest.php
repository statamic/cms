<?php

namespace Tests\Http\View\Composers;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Statamic\Http\View\Composers\JavascriptComposer;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class JavascriptComposerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_provides_crop_aspect_ratios_to_script_config()
    {
        config()->set('statamic.assets.crop_aspect_ratios', [
            '9:16',
            ['label' => 'US Letter', 'ratio' => '8.5:11'],
        ]);

        $user = User::make()
            ->makeSuper()
            ->save();
        $this->actingAs($user);

        $view = app('view')->make('statamic::partials.scripts');

        (new JavascriptComposer)->compose($view);

        $json = Statamic::jsonVariables(request());

        $this->assertSame([
            ['label' => '9:16', 'value' => 9 / 16],
            ['label' => 'US Letter', 'value' => 8.5 / 11],
        ], $json['cropAspectRatios']);
    }

    #[Test]
    public function it_does_not_leak_fallback_locale_json_translations_when_locale_is_english()
    {
        app()->setFallbackLocale('de');
        app()->setLocale('en');
        Lang::addJsonPath(__DIR__.'/../../../__fixtures__/lang-composer-fallback');

        $user = User::make()->makeSuper()->save();
        $this->actingAs($user);

        $view = app('view')->make('statamic::partials.scripts');

        (new JavascriptComposer)->compose($view);

        $json = Statamic::jsonVariables(request());

        $this->assertArrayNotHasKey('*.Only in fallback', $json['translations']);
    }

    #[Test]
    public function it_still_uses_fallback_locale_json_translations_for_non_english_locales()
    {
        app()->setFallbackLocale('de');
        app()->setLocale('fr');
        Lang::addJsonPath(__DIR__.'/../../../__fixtures__/lang-composer-fallback');

        $user = User::make()->makeSuper()->save();
        $this->actingAs($user);

        $view = app('view')->make('statamic::partials.scripts');

        (new JavascriptComposer)->compose($view);

        $json = Statamic::jsonVariables(request());

        $this->assertSame('Nur im Fallback', $json['translations']['*.Only in fallback']);
    }

    #[Test]
    public function it_provides_the_avatar_thumbnail_url_for_assets_in_a_private_container()
    {
        Storage::fake('avatars');
        Storage::disk('avatars')->putFileAs('', UploadedFile::fake()->image('john.jpg'), 'john.jpg');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $userBlueprint = Blueprint::makeFromFields(['avatar' => ['type' => 'assets', 'container' => 'avatars', 'max_files' => 1]]);
        $assetBlueprint = Blueprint::makeFromFields([]);
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($userBlueprint);
        BlueprintRepository::shouldReceive('find')->with('assets/avatars')->andReturn($assetBlueprint);

        $user = User::make()->email('john@example.com')->makeSuper()->set('avatar', 'john.jpg')->save();
        $this->actingAs($user);

        $view = app('view')->make('statamic::partials.scripts');

        (new JavascriptComposer)->compose($view);

        $json = Statamic::jsonVariables(request());

        $this->assertSame('http://localhost/cp/thumbnails/YXZhdGFyczo6am9obi5qcGc=/small/square', $json['user']['avatar']);
    }
}
