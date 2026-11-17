<?php

namespace Tests\Feature\Utilities;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\SearchIndexUpdated;
use Statamic\Facades\User;
use Statamic\Search\Index;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateSearchTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake([SearchIndexUpdated::class]);
    }

    public function tearDown(): void
    {
        // Reset the static state of the Index class
        Index::resolveNameUsing(null);

        parent::tearDown();
    }

    private function setUpIndexes()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        config(['statamic.search.indexes' => [
            'test' => ['driver' => 'null'],
            'localized' => ['driver' => 'null', 'sites' => ['en', 'fr']],
        ]]);
    }

    private function update(array $indexes)
    {
        return $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->post(cp_route('utilities.search.update'), ['indexes' => $indexes]);
    }

    private function assertIndexesUpdated(array $names)
    {
        $updated = Event::dispatched(SearchIndexUpdated::class)->map(fn ($event) => $event[0]->index->name());

        $this->assertEquals($names, $updated->all());
    }

    #[Test]
    public function it_updates_the_selected_indexes()
    {
        $this->setUpIndexes();

        $this->update(['test::null', 'localized_fr::fr'])->assertSessionHas('success');

        $this->assertIndexesUpdated(['test', 'localized_fr']);
    }

    #[Test]
    public function it_updates_the_selected_indexes_when_the_names_have_been_customized()
    {
        // When a custom resolver prefixes the name, the resolved name can no longer be
        // reversed back into the configured handle by string manipulation.
        Index::resolveNameUsing(fn ($name, $locale) => 'prefix_'.($locale ? $name.'_'.$locale : $name));

        $this->setUpIndexes();

        $this->update(['prefix_test::null', 'prefix_localized_fr::fr'])->assertSessionHas('success');

        $this->assertIndexesUpdated(['prefix_test', 'prefix_localized_fr']);
    }

    #[Test]
    public function it_errors_when_the_index_doesnt_exist()
    {
        $this->setUpIndexes();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Search index [unknown] is not defined.');

        $this->withoutExceptionHandling()->update(['unknown::null']);
    }
}
