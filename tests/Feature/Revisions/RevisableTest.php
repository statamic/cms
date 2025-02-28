<?php

namespace Tests\Feature\Revisions;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Revision;
use Statamic\Revisions\Revisable;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RevisableTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $revisable;

    public function setUp(): void
    {
        parent::setUp();

        config(['statamic.revisions' => [
            'enabled' => true,
            'path' => __DIR__.'/__fixtures__',
        ]]);

        $this->revisable = new class
        {
            use Revisable;

            protected function revisionKey()
            {
                return '123';
            }

            protected function revisionAttributes()
            {
                return [
                    'id' => 123
                ];
            }

            public function makeFromRevision($revision)
            {
                return new self;
            }
        };
    }

    #[Test]
    public function has_revisions()
    {
        $this->assertTrue($this->revisable->hasRevisions());
    }

    #[Test]
    public function sets_publish_at_from_options()
    {
        Carbon::setTestNow($now = now());

        $this->revisable->createRevision(['publish_at' => $now]);

        $revision = $this->revisable->latestRevision();
        Revision::delete($revision);

        $this->assertEquals($revision->publishAt()->timestamp, $now->timestamp);
    }
}
