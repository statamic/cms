<?php

namespace Tests\Licensing;

trait LicenseTests
{
    /** @test */
    public function it_gets_the_response()
    {
        $license = $this->license(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $license->response());
    }

    /** @test */
    public function it_checks_if_its_valid()
    {
        $this->assertTrue($this->license(['valid' => true])->valid());
        $this->assertFalse($this->license(['valid' => false])->valid());
    }

    /** @test */
    public function it_gets_the_invalid_reason()
    {
        $license = $this->license(['reason' => 'nope']);

        $this->assertEquals(
            'statamic::messages.licensing_error_nope',
            $license->invalidReason()
        );
    }

    /** @test */
    public function invalid_reason_is_null_if_there_isnt_one()
    {
        $license = $this->license(['foo' => 'bar']);

        $this->assertNull($license->invalidReason());
    }
}
