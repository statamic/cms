<?php

namespace Tests\Query;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\TestCase;

class FakesQueriesTest extends TestCase
{
    #[Test]
    public function it_supports_to_sql()
    {
        $query = User::query()->where('name', 'Jack');
        $this->assertSame('select * from users where name = ?', $query->toSql());
    }

    #[Test]
    public function it_supports_to_raw_sql()
    {
        $query = User::query()->where('name', 'Jack');
        $this->assertSame("select * from users where name = 'Jack'", $query->toRawSql());
    }

    #[Test]
    public function it_converts_date_bindings_to_the_app_timezone_in_raw_sql()
    {
        Carbon::setTestNow('2026-07-04 10:00:00');

        $query = User::query()->where('created_at', Carbon::today('Europe/Zurich'));

        $this->assertSame(
            "select * from users where created_at = '2026-07-03 22:00:00'",
            $query->toRawSql()
        );

        Carbon::setTestNow();
    }

    #[Test]
    public function it_supports_dump_raw_sql()
    {
        $query = User::query()->where('name', 'Jack');
        $this->assertSame($query, $query->dumpRawSql());
    }

    #[Test]
    public function it_supports_dd_raw_sql()
    {
        $query = User::query()->where('name', 'Jack');
        $this->assertIsCallable([$query, 'ddRawSql']);
    }

    #[Test]
    public function it_supports_ray()
    {
        $query = User::query()->where('name', 'Jack');
        $this->assertIsCallable([$query, 'ray']);
    }
}
