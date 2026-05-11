<?php

namespace Tests\Http\View\Composers;

use PHPUnit\Framework\Attributes\Test;
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
}
