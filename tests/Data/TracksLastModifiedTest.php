<?php

namespace Tests\Data;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Tests\TestCase;

class TracksLastModifiedTest extends TestCase
{
    private $entry;
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $collection = Facades\Collection::make('pages');
        $collection->save();

        $this->entry = Facades\Entry::make()
            ->collection($collection)
            ->locale(Facades\Site::default()->handle());

        $this->user = Facades\User::make()->email('hoff@baywatch.com')->save();
    }

    public function tearDown(): void
    {
        Facades\Collection::all()->each->delete();
        Facades\Entry::all()->each->delete();
        Facades\User::all()->each->delete();

        parent::tearDown();
    }

    #[Test]
    public function it_gets_last_updated_at_from_file()
    {
        $this->assertFalse($this->entry->has('updated_at'));
        $this->assertEquals($this->entry->fileLastModified()->timestamp, $this->entry->lastModified()->timestamp);
    }

    #[Test]
    public function it_updates_and_gets_last_updated_with_user()
    {
        $this->assertFalse($this->entry->has('updated_at'));
        $this->assertNull($this->entry->lastModifiedBy());

        $this->entry->updateLastModified($this->user)->save();

        $this->assertTrue($this->entry->has('updated_at'));
        $this->assertEquals(Carbon::parse($this->entry->get('updated_at'))->timestamp, $this->entry->lastModified()->timestamp);
        $this->assertTrue($this->entry->has('updated_by'));
        $this->assertEquals('hoff@baywatch.com', $this->entry->lastModifiedBy()->email());
    }

    #[Test]
    public function it_updates_and_gets_last_updated_without_user()
    {
        $this->assertFalse($this->entry->has('updated_at'));
        $this->assertNull($this->entry->lastModifiedBy());

        $this->entry->updateLastModified()->save();

        $this->assertTrue($this->entry->has('updated_at'));
        $this->assertEquals(Carbon::parse($this->entry->get('updated_at'))->timestamp, $this->entry->lastModified()->timestamp);
        $this->assertFalse($this->entry->has('updated_by'));
        $this->assertNull($this->entry->lastModifiedBy());
    }

    #[Test]
    public function it_updates_and_changes_last_updated_by_user()
    {
        $this->entry->set('updated_by', $this->user->id())->save();

        $this->assertEquals('hoff@baywatch.com', $this->entry->lastModifiedBy()->email());

        $casey = Facades\User::make()->email('casey@baywatch.com')->save();

        $this->entry->updateLastModified($casey)->save();

        $this->assertEquals('casey@baywatch.com', $this->entry->lastModifiedBy()->email());
    }

    #[Test]
    public function it_updates_and_nulls_last_updated_by_user()
    {
        $this->entry->set('updated_by', $this->user->id())->save();

        $this->assertEquals('hoff@baywatch.com', $this->entry->lastModifiedBy()->email());

        $this->entry->updateLastModified()->save();

        $this->assertFalse($this->entry->has('updated_by'));
        $this->assertNull($this->entry->lastModifiedBy());
    }

    #[Test]
    public function it_can_touch_item_similar_to_eloquent()
    {
        $this->assertFalse($this->entry->has('updated_at'));
        $this->assertFalse($this->entry->has('updated_by'));

        $touched = $this->entry->touch();

        $this->assertNull($touched);
        $this->assertTrue($this->entry->has('updated_at'));
        $this->assertEquals(Carbon::parse($this->entry->get('updated_at'))->timestamp, $this->entry->lastModified()->timestamp);
        $this->assertFalse($this->entry->has('updated_by'));
        $this->assertNull($this->entry->lastModifiedBy());
    }

    #[Test]
    public function it_can_touch_item_with_user()
    {
        $this->assertFalse($this->entry->has('updated_at'));
        $this->assertFalse($this->entry->has('updated_by'));

        $touched = $this->entry->touch($this->user);

        $this->assertNull($touched);
        $this->assertTrue($this->entry->has('updated_at'));
        $this->assertEquals(Carbon::parse($this->entry->get('updated_at'))->timestamp, $this->entry->lastModified()->timestamp);
        $this->assertTrue($this->entry->has('updated_by'));
        $this->assertEquals('hoff@baywatch.com', $this->entry->lastModifiedBy()->email());
    }

    #[Test]
    public function it_will_not_update_when_config_disables_last_update_tracking()
    {
        $this->assertFalse($this->entry->has('updated_at'));
        $this->assertFalse($this->entry->has('updated_by'));

        Facades\Config::set('statamic.system.track_last_update', false);

        $this->entry->updateLastModified($this->user)->save();
        $this->entry->touch($this->user);

        $this->assertFalse($this->entry->has('updated_at'));
        $this->assertFalse($this->entry->has('updated_by'));
    }
}
