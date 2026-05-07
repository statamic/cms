<?php

namespace Tests\Query;

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
