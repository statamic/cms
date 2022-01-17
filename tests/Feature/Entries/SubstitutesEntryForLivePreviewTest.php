<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\Token;
use Statamic\Tokens\Handlers\LivePreviewEntry;
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
        $this->viewShouldReturnRaw('test', '{{ collection:test }}{{ title }} {{ foo }} {{ /collection:test }}');
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        Route::view('/test', 'test')->middleware('statamic.web');
    }

    /** @test */
    public function it_substitutes()
    {
        $token = Token::make('test-token', LivePreviewEntry::class, ['entry' => '2', 'data' => ['title' => 'Substituted title', 'foo' => 'Substituted foo']]);
        Token::shouldReceive('find')->with('test-token')->andReturn($token)->once();

        $this->get('/test?token=test-token')->assertSeeInOrder([
            'Alfa',
            'Alfa foo',
            'Substituted title',
            'Substituted foo',
            'Charlie',
            'Charlie foo',
        ]);
    }

    /** @test */
    public function it_doesnt_substitute()
    {
        Token::shouldReceive('find')->with('invalid-token')->andReturnNull()->once();

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
