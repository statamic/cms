<?php

namespace Tests\Revisions;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Revisions\RevisionRepository;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RevisableTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $repo;

    protected function setUp(): void
    {
        parent::setUp();
        config(['statamic.revisions.path' => __DIR__.'/__fixtures__']);
        $this->repo = (new RevisionRepository);
    }

    #[Test]
    public function it_gets_non_revisable_fields()
    {
        $blueprint = Blueprint::makeFromFields([
            'revisable' => ['type' => 'text'],
            'non_revisable' => ['type' => 'text', 'revisable' => false],
        ]);
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect(['blog' => $blueprint]));
        Collection::make('blog')->save();

        $entry = (new Entry)->collection('blog')->id('123');

        $this->assertEquals(['non_revisable'], $entry->nonRevisableFields());
    }

    #[Test]
    public function it_sets_proper_revision_attributes()
    {
        $blueprint = Blueprint::makeFromFields([
            'revisable' => ['type' => 'text'],
            'non_revisable' => ['type' => 'text', 'revisable' => false],
        ]);
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect(['blog' => $blueprint]));
        Collection::make('blog')->save();

        $entry = (new Entry)
            ->collection('blog')
            ->id('123')
            ->set('revisable', 'see me')
            ->set('non_revisable', "don't see me");

        $this->assertEquals(['revisable' => 'see me'], $entry->makeRevision()->attributes()['data']);
    }

    #[Test]
    public function it_can_make_entry_from_revision()
    {
        $blueprint = Blueprint::makeFromFields([
            'revisable' => ['type' => 'text'],
            'non_revisable' => ['type' => 'text', 'revisable' => false],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect(['blog' => $blueprint]));
        Collection::make('blog')->save();

        $entry = (new Entry)->collection('blog')->id('123');
        $entry->set('revisable', 'override me')->set('non_revisable', "don't override me");

        $revision = $this->repo->whereKey('123')->first();

        $this->assertEquals(
            collect([
                'revisable' => 'overridden',
                'non_revisable' => "don't override me",
            ]),
            $entry->makeFromRevision($revision)->data()
        );
    }
}
