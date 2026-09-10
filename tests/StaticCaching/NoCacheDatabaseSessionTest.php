<?php

namespace Tests\StaticCaching;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\NoCache\DatabaseRegion;
use Statamic\StaticCaching\NoCache\DatabaseSession;
use Statamic\StaticCaching\NoCache\StringRegion;
use Tests\TestCase;

class NoCacheDatabaseSessionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('nocache_regions', function (Blueprint $table) {
            $table->string('key')->index()->primary();
            $table->string('url')->index();
            $table->longText('region');
            $table->timestamps();
        });
    }

    public function tearDown(): void
    {
        Schema::dropIfExists('nocache_regions');

        parent::tearDown();
    }

    #[Test]
    public function it_stores_regions_base64_encoded()
    {
        $session = new DatabaseSession('http://localhost/test');

        $region = $session->pushRegion('the contents', ['foo' => 'bar'], '.html');

        $stored = DatabaseRegion::where('key', $region->key())->first()->region;

        $this->assertEquals(base64_encode(serialize($region)), $stored);
    }

    #[Test]
    public function it_reads_base64_encoded_regions()
    {
        $session = new DatabaseSession('http://localhost/test');

        $region = $session->pushRegion('the contents', ['foo' => 'bar'], '.html');

        $retrieved = $session->region($region->key());

        $this->assertInstanceOf(StringRegion::class, $retrieved);
        $this->assertEquals($region->key(), $retrieved->key());
        $this->assertEquals(['foo' => 'bar'], $retrieved->context());
    }

    #[Test]
    public function it_reads_legacy_non_base64_encoded_regions()
    {
        // Simulate a row written before base64 encoding was introduced
        // (e.g. an existing PostgreSQL or SQLite install that ran the
        // nocache migration prior to this fix).
        $session = new DatabaseSession('http://localhost/test');
        $region = new StringRegion($session, 'the contents', ['foo' => 'bar'], '.html');

        DatabaseRegion::create([
            'key' => $region->key(),
            'url' => md5('http://localhost/test'),
            'region' => serialize($region),
        ]);

        $retrieved = $session->region($region->key());

        $this->assertInstanceOf(StringRegion::class, $retrieved);
        $this->assertEquals($region->key(), $retrieved->key());
        $this->assertEquals(['foo' => 'bar'], $retrieved->context());
    }
}
