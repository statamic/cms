<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\CP\LivePreview;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Entry;
use Statamic\Facades\Token;
use Statamic\View\View;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SubstitutesEntryForLivePreviewTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        // The array driver would store entry instances in memory, and we could get false-positive
        // tests by just modifying the entry without actually performing the substitution.
        config(['cache.default' => 'file']);

        EntryFactory::collection('test')->id('1')->slug('alfa')->data(['title' => 'Alfa', 'foo' => 'Alfa foo'])->create();
        EntryFactory::collection('test')->id('2')->slug('bravo')->data(['title' => 'Bravo', 'foo' => 'Bravo foo'])->create();
        EntryFactory::collection('test')->id('3')->slug('charlie')->data(['title' => 'Charlie', 'foo' => 'Charlie foo'])->create();

        $this->withFakeViews();

        $template = <<<'EOT'
            {{ collection:test scope="article" }}
                {{ title }}
                {{ foo }}
            {{ /collection:test }}
EOT;
        $this->viewShouldReturnRaw('test', $template);
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Use our View::make() to make sure the Cascade is used.
        // We'd use Route::statamic() but it isn't available at this point.
        Route::get('/test', fn () => View::make('test'))->middleware('statamic.web');
    }

    #[Test]
    public function it_substitutes()
    {
        $substitute = EntryFactory::collection('test')->id('2')->slug('charlie')->data(['title' => 'Substituted title', 'foo' => 'Substituted foo'])->make();
        LivePreview::tokenize('test-token', $substitute);

        $this->assertEquals('Bravo', Entry::find('2')->get('title')); // Check that the test didn't somehow override the real entry accidentally.

        $this->get('/test?token=test-token')
            ->assertSeeInOrder([
                'Alfa',
                'Alfa foo',
                'Substituted title',
                'Substituted foo',
                'Charlie',
                'Charlie foo',
            ])
            ->assertHeader('X-Statamic-Live-Preview', true);
    }

    #[Test]
    public function it_doesnt_substitute()
    {
        Token::shouldReceive('find')->with('invalid-token')->andReturnNull();

        $this->get('/test?token=invalid-token')->assertSeeInOrder([
            'Alfa',
            'Alfa foo',
            'Bravo',
            'Bravo foo',
            'Charlie',
            'Charlie foo',
        ]);
    }
}
