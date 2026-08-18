<?php

namespace Tests\Licensing;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Statamic\Licensing\Outpost;
use Statamic\Licensing\Radio;
use Tests\TestCase;

class RadioTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Cache::store('outpost')->flush();
    }

    #[Test]
    public function it_contacts_the_outpost()
    {
        $outpost = $this->mock(Outpost::class);
        $outpost->shouldReceive('radio')->once();

        (new Radio($outpost))->ping();
    }

    #[Test]
    public function it_throttles_pings()
    {
        $outpost = $this->mock(Outpost::class);
        $outpost->shouldReceive('radio')->twice();

        $radio = new Radio($outpost);

        $radio->ping();
        $radio->ping(); // Throttled.

        Carbon::setTestNow(now()->addSeconds(Radio::PING_INTERVAL + 1));

        $radio->ping();
    }

    #[Test]
    public function it_swallows_outpost_exceptions()
    {
        $outpost = $this->mock(Outpost::class);
        $outpost->shouldReceive('radio')->once()->andThrow(new RuntimeException('nope'));

        $radio = new Radio($outpost);

        $radio->ping();
        $radio->ping(); // Still throttled after a failure.
    }

    #[Test]
    public function it_pings_during_cp_requests()
    {
        $request = Request::create('/cp/dashboard');

        $this->assertTrue($this->radio()->shouldPingDuringRequest($request));
        $this->assertFalse($this->radio()->shouldPingAfterResponse($request));
    }

    #[Test]
    public function it_does_not_ping_after_front_end_responses_during_tests()
    {
        $request = Request::create('/about');

        $this->assertFalse($this->radio()->shouldPingDuringRequest($request));
        $this->assertFalse($this->radio()->shouldPingAfterResponse($request));
        $this->assertTrue($this->radio()->shouldPingRequest($request));
    }

    #[Test]
    public function it_skips_glide_requests()
    {
        $request = Request::create('/img/foo.jpg');

        $this->assertFalse($this->radio()->shouldPingRequest($request));
    }

    #[Test]
    public function it_skips_site_prefixed_glide_requests()
    {
        $request = Request::create('/fr/img/foo.jpg');

        $this->assertFalse($this->radio()->shouldPingRequest($request));
    }

    #[Test]
    public function it_does_not_ping_commands_during_tests()
    {
        $this->assertFalse($this->radio()->shouldPingCommand('statamic:stache:clear'));
        $this->assertFalse($this->radio()->shouldPingCommand('migrate'));
    }

    #[Test]
    #[DataProvider('ignoredCommandsProvider')]
    public function it_ignores_noisy_commands(?string $command)
    {
        $this->assertTrue($this->radio()->isCommandIgnored($command));
    }

    public static function ignoredCommandsProvider(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'list' => ['list'],
            'help' => ['help'],
            'tinker' => ['tinker'],
            'serve' => ['serve'],
            'schedule:run' => ['schedule:run'],
            'schedule:work' => ['schedule:work'],
            'queue:work' => ['queue:work'],
            'queue:listen' => ['queue:listen'],
            'horizon' => ['horizon'],
            'horizon:work' => ['horizon:work'],
            'octane:start' => ['octane:start'],
            'reverb:start' => ['reverb:start'],
            'test' => ['test'],
            'pest' => ['pest'],
        ];
    }

    #[Test]
    #[DataProvider('allowedCommandsProvider')]
    public function it_does_not_ignore_normal_commands(string $command)
    {
        $this->assertFalse($this->radio()->isCommandIgnored($command));
    }

    public static function allowedCommandsProvider(): array
    {
        return [
            'statamic:stache:clear' => ['statamic:stache:clear'],
            'statamic:install' => ['statamic:install'],
            'migrate' => ['migrate'],
            'statamic:make:user' => ['statamic:make:user'],
            'about' => ['about'],
        ];
    }

    private function radio(): Radio
    {
        return new Radio($this->mock(Outpost::class));
    }
}
