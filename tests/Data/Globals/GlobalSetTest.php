<?php

namespace Tests\Data\Globals;

use Illuminate\Support\Facades\Event;
use Statamic\Events\GlobalSetCreated;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\GlobalSetSaving;
use Statamic\Facades\GlobalSet as GlobalSetFacade;
use Statamic\Facades\Site;
use Statamic\Globals\GlobalSet;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GlobalSetTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_file_contents_for_saving_with_a_single_site()
    {
        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            ],
        ]);

        $set = (new GlobalSet)->title('The title');

        $variables = $set->makeLocalization('en')->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
        ]);

        $set->addLocalization($variables);

        $expected = <<<'EOT'
title: 'The title'
data:
  array:
    - 'first one'
    - 'second one'
  string: 'The string'

EOT;
        $this->assertEquals($expected, $set->fileContents());
    }

    /** @test */
    public function it_gets_file_contents_for_saving_with_multiple_sites()
    {
        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);

        $set = (new GlobalSet)->title('The title');

        // We set the data but it's basically irrelevant since it won't get saved to this file.
        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });
        $set->in('fr', function ($loc) {
            $loc->data([
                'array' => ['le first one', 'le second one'],
                'string' => 'Le string',
            ]);
        });

        $expected = <<<'EOT'
title: 'The title'

EOT;
        $this->assertEquals($expected, $set->fileContents());
    }

    /** @test */
    public function it_saves_through_the_api()
    {
        Event::fake();

        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);

        $set = (new GlobalSet)->title('SEO Settings');

        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });

        $set->save();

        Event::assertDispatched(GlobalSetSaving::class, function ($event) use ($set) {
            return $event->globals === $set;
        });

        Event::assertDispatched(GlobalSetCreated::class, function ($event) use ($set) {
            return $event->globals === $set;
        });

        Event::assertDispatched(GlobalSetSaved::class, function ($event) use ($set) {
            return $event->globals === $set;
        });
    }

    /** @test */
    public function it_dispatches_global_set_created_only_once()
    {
        Event::fake();

        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);

        $set = (new GlobalSet)->title('SEO Settings');

        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });

        GlobalSetFacade::shouldReceive('save')->with($set);
        GlobalSetFacade::shouldReceive('find')->with($set->handle())->times(3)->andReturn(null, $set, $set);

        $set->save();
        $set->save();
        $set->save();

        Event::assertDispatched(GlobalSetSaved::class, 3);
        Event::assertDispatched(GlobalSetCreated::class, 1);
    }

    /** @test */
    public function it_saves_quietly()
    {
        Event::fake();

        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);

        $set = (new GlobalSet)->title('SEO Settings');

        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });

        $set->saveQuietly();

        Event::assertNotDispatched(GlobalSetSaving::class);
        Event::assertNotDispatched(GlobalSetSaved::class);
        Event::assertNotDispatched(GlobalSetCreated::class);
    }

    /** @test */
    public function if_saving_event_returns_false_the_global_set_doesnt_save()
    {
        Event::fake([GlobalSetSaved::class]);

        Event::listen(GlobalSetSaving::class, function () {
            return false;
        });

        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);

        $set = (new GlobalSet)->title('SEO Settings');

        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });

        $set->save();

        Event::assertNotDispatched(GlobalSetSaved::class);
    }
}
