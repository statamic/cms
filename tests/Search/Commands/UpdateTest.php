<?php

namespace Tests\Search\Commands;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Search;
use Statamic\Search\Commands\Update;
use Statamic\Search\Index;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    public function tearDown(): void
    {
        // Reset the static state of the Index class
        Index::resolveNameUsing(null);

        parent::tearDown();
    }

    private function fakeIndex()
    {
        $index = Mockery::mock(Index::class);
        $index->shouldReceive('name')->andReturn('test');
        $index->shouldReceive('update')->once();

        Search::shouldReceive('indexes')->andReturn(collect(['test' => $index]));

        return $index;
    }

    private function setUpIndexes()
    {
        $this->setSites([
            'en' => ['url' => '/'],
            'fr' => ['url' => '/fr/'],
        ]);

        config(['statamic.search.indexes' => [
            'test' => ['driver' => 'null', 'sites' => ['en', 'fr']],
            'cp' => ['driver' => 'null'],
        ]]);
    }

    #[Test]
    public function it_updates_the_localized_indexes_matching_the_configured_handle()
    {
        $this->setUpIndexes();

        $this->artisan(Update::class, ['index' => 'test'])
            ->expectsOutputToContain('Index test_en updated.')
            ->expectsOutputToContain('Index test_fr updated.')
            ->doesntExpectOutputToContain('Index cp updated.')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_updates_the_localized_indexes_when_the_names_have_been_customized()
    {
        // When a custom resolver prefixes the name, the resolved name can no longer be
        // reversed back into the configured handle by string manipulation.
        Index::resolveNameUsing(fn ($name, $locale) => 'local_'.$name.'_'.$locale);

        $this->setUpIndexes();

        // The argument is the handle as it appears in the config, so it should still
        // match both localized versions, and it should leave the cp index alone.
        $this->artisan(Update::class, ['index' => 'test'])
            ->expectsOutputToContain('Index local_test_en updated.')
            ->expectsOutputToContain('Index local_test_fr updated.')
            ->doesntExpectOutputToContain('Index local_cp_ updated.')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_uses_the_configured_queue_connection_by_default()
    {
        $index = $this->fakeIndex();
        $index->shouldReceive('onConnection')->never();

        $this->artisan(Update::class, ['index' => 'test'])->assertExitCode(0);
    }

    #[Test]
    public function it_uses_the_queue_connection_from_the_queue_option()
    {
        $index = $this->fakeIndex();
        $index->shouldReceive('onConnection')->once()->with('sync')->andReturnSelf();

        $this->artisan(Update::class, ['index' => 'test', '--queue' => 'sync'])->assertExitCode(0);
    }

    #[Test]
    public function it_errors_when_the_index_doesnt_exist()
    {
        $this->setUpIndexes();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Index [unknown] does not exist.');

        $this->artisan(Update::class, ['index' => 'unknown'])->run();
    }
}
