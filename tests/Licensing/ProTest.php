<?php

namespace Tests\Licensing;

use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Licensing\Pro;
use Tests\TestCase;

class ProTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['statamic.api.enabled' => false]);

        $this->pro = new Pro;
    }

    private function assertPro()
    {
        $this->assertTrue($this->pro->check());
    }

    private function assertNotPro()
    {
        $this->assertFalse($this->pro->check());
    }

    /** @test */
    public function multiple_users_triggers_pro()
    {
        User::shouldReceive('count')->andReturn(0, 1, 2);

        $this->assertNotPro();
        $this->assertNotPro();
        $this->assertPro();
    }

    /** @test */
    public function rest_api_triggers_pro()
    {
        config(['statamic.api.enabled' => false]);
        $this->assertNotPro();

        config(['statamic.api.enabled' => true]);
        $this->assertPro();
    }

    /** @test */
    public function multiple_sites_triggers_pro()
    {
        Site::setConfig(['sites' => ['one' => []]]);
        $this->assertNotPro();

        Site::setConfig(['sites' => ['one' => [], 'two' => []]]);
        $this->assertPro();
    }

    /** @test */
    public function revisions_triggers_pro()
    {
        config(['statamic.revisions.enabled' => false]);
        $this->assertNotPro();

        config(['statamic.revisions.enabled' => true]);
        $this->assertPro();
    }

    /** @test */
    public function multiple_forms_triggers_pro()
    {
        Form::shouldReceive('count')->andReturn(0, 1, 2);

        $this->assertNotPro();
        $this->assertNotPro();
        $this->assertPro();
    }
}
