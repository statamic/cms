<?php

namespace Tests\Listeners;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\Listeners\ClearState;
use Statamic\Statamic;
use Tests\TestCase;

class ClearStateTest extends TestCase
{
    private array $originalJsonVariables;
    private ?array $originalJsonVariablesSnapshot;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->booted(function () {
            Route::get('/provide-to-script', function () {
                Statamic::provideToScript(['request' => 'value']);

                return 'ok';
            });
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalJsonVariables = $this->getStatamicStatic('jsonVariables');
        $this->originalJsonVariablesSnapshot = $this->getStatamicStatic('jsonVariablesSnapshot');
    }

    public function tearDown(): void
    {
        $this->setStatamicStatic('jsonVariables', $this->originalJsonVariables);
        $this->setStatamicStatic('jsonVariablesSnapshot', $this->originalJsonVariablesSnapshot);
        Statamic::$isRenderingCpException = false;
        Breadcrumbs::$pushed = [];

        parent::tearDown();
    }

    #[Test]
    public function it_resets_cp_exception_and_breadcrumb_state()
    {
        Statamic::$isRenderingCpException = true;
        Breadcrumbs::$pushed = [new Breadcrumb(text: 'Test')];

        (new ClearState)->handle();

        $this->assertFalse(Statamic::$isRenderingCpException);
        $this->assertSame([], Breadcrumbs::$pushed);
    }

    #[Test]
    public function it_registers_the_snapshot_middleware()
    {
        $this->assertTrue(
            $this->app[\Illuminate\Contracts\Http\Kernel::class]
                ->hasMiddleware(\Statamic\Http\Middleware\SnapshotJsonVariables::class)
        );
    }

    #[Test]
    public function it_restores_json_variables_to_the_snapshot()
    {
        $this->setStatamicStatic('jsonVariablesSnapshot', null);
        Statamic::provideToScript(['boot' => 'value']);
        Statamic::snapshotJsonVariables();
        Statamic::provideToScript(['request' => 'value']);

        (new ClearState)->handle();

        $variables = Statamic::jsonVariables(request());

        $this->assertSame('value', $variables['boot']);
        $this->assertArrayNotHasKey('request', $variables);
    }

    #[Test]
    public function it_leaves_json_variables_alone_when_no_snapshot_was_taken()
    {
        $this->setStatamicStatic('jsonVariablesSnapshot', null);
        Statamic::provideToScript(['foo' => 'bar']);

        (new ClearState)->handle();

        $this->assertSame('bar', Statamic::jsonVariables(request())['foo']);
    }

    #[Test]
    public function json_variables_registered_before_a_request_survive_the_reset_but_ones_registered_during_it_do_not()
    {
        $this->setStatamicStatic('jsonVariablesSnapshot', null);
        Statamic::provideToScript(['boot' => 'value']);

        $this->get('/provide-to-script')->assertOk();

        $variables = Statamic::jsonVariables(request());
        $this->assertSame('value', $variables['boot']);
        $this->assertArrayNotHasKey('request', $variables);

        $this->get('/provide-to-script')->assertOk();

        $variables = Statamic::jsonVariables(request());
        $this->assertSame('value', $variables['boot']);
        $this->assertArrayNotHasKey('request', $variables);
    }

    private function getStatamicStatic(string $property)
    {
        return (new ReflectionProperty(Statamic::class, $property))->getValue();
    }

    private function setStatamicStatic(string $property, $value): void
    {
        (new ReflectionProperty(Statamic::class, $property))->setValue(null, $value);
    }
}
