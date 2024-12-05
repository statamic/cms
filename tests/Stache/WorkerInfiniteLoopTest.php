<?php

namespace Tests\Stache;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class WorkerInfiniteLoopTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function infinite_loops_are_prevented_when_running_workers()
    {
        // NOTE: We are not using the `config(['cache.default' => 'file'])` override as that
        //       will cause an infinite loop on failure instead of a segfault.

        // `Statamic::isWorker()` should return false by default.
        $this->assertFalse(Statamic::isWorker());

        // Swap the request with one that will cause `Statamic::isWorker()` to return `true`.
        // NOTE: Cannot use `Tests\Fakes\FakeArtisanRequest` as that does not have the cookies
        //       object initialised and `auth()->user()` will throw an Exception.
        Request::swap(request()->duplicate(server: [
            'argv' => ['artisan', 'queue:work'],
            'argc' => 2,
        ]));

        // `Statamic::isWorker()` should return true when being called from any command beginning with `queue:`.
        $this->assertTrue(Statamic::isWorker());

        Collection::make('test')->save();
        EntryFactory::id('alfa-id')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        EntryFactory::id('bravo-id')->collection('test')->slug('bravo')->data(['title' => 'Bravo'])->create();
        EntryFactory::id('charlie-id')->collection('test')->slug('charlie')->data(['title' => 'Charlie'])->create();
        EntryFactory::id('donkus-id')->collection('test')->slug('donkus')->data(['title' => 'Donkus'])->create();
        EntryFactory::id('eggbert-id')->collection('test')->slug('eggbert')->data(['title' => 'Eggbert'])->create();
    }
}
