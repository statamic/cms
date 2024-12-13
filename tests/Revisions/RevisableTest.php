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

    private $entry;

    protected function setUp(): void
    {
        parent::setUp();

        // $this->repo = (new RevisionRepository);
    }

    #[Test]
    public function it_gets_revisions_and_excludes_working_copies()
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
}
