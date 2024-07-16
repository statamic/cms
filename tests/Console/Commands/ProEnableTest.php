<?php

namespace Tests\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Statamic;
use Tests\TestCase;

class ProEnableTest extends TestCase
{
    private $files;
    private $envPath;
    private $editionsPath;
    private $defaultEnvContents;
    private $defaultEditionsContents;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        $this->envPath = base_path('.env');
        $this->editionsPath = config_path('statamic/editions.php');

        $this->defaultEnvContents = <<<'ENV'
APP_NAME=Statamic
STATAMIC_PRO_ENABLED=false
STATAMIC_LICENSE_KEY=
ENV;

        $this->defaultEditionsContents = $this->files->get(__DIR__.'/../../../config/editions.php');

        $this->files->put($this->envPath, $this->defaultEnvContents);

        $this->files->makeDirectory(dirname($this->editionsPath), 0777, true, true);
        $this->files->put($this->editionsPath, $this->defaultEditionsContents);
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.editions.pro', false);
    }

    public function tearDown(): void
    {
        $this->files->delete($this->envPath);
        $this->files->delete($this->editionsPath);

        parent::tearDown();
    }

    #[Test]
    public function it_can_enable_pro_by_updating_existing_var_in_env()
    {
        $this->assertFalse(Statamic::pro());
        $this->assertEquals($this->defaultEditionsContents, $this->files->get($this->editionsPath));
        $this->assertEquals($this->defaultEnvContents, $this->files->get($this->envPath));

        $this->artisan('statamic:pro:enable');

        $this->assertTrue(Statamic::pro());
        $this->assertEquals($this->defaultEditionsContents, $this->files->get($this->editionsPath));
        $this->assertEquals(<<<'ENV'
APP_NAME=Statamic
STATAMIC_PRO_ENABLED=true
STATAMIC_LICENSE_KEY=
ENV, $this->files->get($this->envPath));
    }

    #[Test]
    public function it_can_enable_pro_by_appending_to_env()
    {
        $this->files->put($this->envPath, $this->defaultEnvContents = <<<'ENV'
APP_NAME=Statamic
STATAMIC_LICENSE_KEY=
ENV);

        $this->assertFalse(Statamic::pro());
        $this->assertEquals($this->defaultEditionsContents, $this->files->get($this->editionsPath));

        $this->artisan('statamic:pro:enable');

        $this->assertTrue(Statamic::pro());
        $this->assertEquals($this->defaultEditionsContents, $this->files->get($this->editionsPath));
        $this->assertEquals(<<<'ENV'
APP_NAME=Statamic
STATAMIC_LICENSE_KEY=
STATAMIC_PRO_ENABLED=true
ENV, $this->files->get($this->envPath));
    }

    #[Test]
    public function if_config_is_not_referencing_env_var_it_should_prompt_user_to_run_with_update_config_option()
    {
        $this->files->put($this->editionsPath, $this->defaultEditionsContents = <<<'EDITIONS'
<?php

return [

    'pro' => env('WRONG!!!', false),

    'addons' => [
        //
    ],

];
EDITIONS);

        $this->assertFalse(Statamic::pro());
        $this->assertEquals($this->defaultEnvContents, $this->files->get($this->envPath));

        $this
            ->artisan('statamic:pro:enable')
            ->expectsOutput('Please re-run this command with the `--update-config` option.');

        // Though it should still update .env
        $this->assertEquals(<<<'ENV'
APP_NAME=Statamic
STATAMIC_PRO_ENABLED=true
STATAMIC_LICENSE_KEY=
ENV, $this->files->get($this->envPath));

        // Pro should not be enabled in the in-memory config, because config is not properly referencing .env var yet
        $this->assertFalse(Statamic::pro());
    }

    public static function hardcodedBooleans()
    {
        return [
            'true' => ['true'],
            'false' => ['false'],
        ];
    }

    #[Test]
    #[DataProvider('hardcodedBooleans')]
    public function it_can_update_editions_config_to_reference_env_var($boolean)
    {
        $this->files->put($this->editionsPath, <<<"EDITIONS"
<?php

return [

    'pro' => $boolean,

    'addons' => [
        //
    ],

];

EDITIONS);

        $this->assertFalse(Statamic::pro());
        $this->assertEquals($this->defaultEnvContents, $this->files->get($this->envPath));

        $this->artisan('statamic:pro:enable', ['--update-config' => true]);

        $this->assertTrue(Statamic::pro());
        $this->assertEquals($this->defaultEditionsContents, $this->files->get($this->editionsPath));
        $this->assertEquals(<<<'ENV'
APP_NAME=Statamic
STATAMIC_PRO_ENABLED=true
STATAMIC_LICENSE_KEY=
ENV, $this->files->get($this->envPath));
    }

    #[Test]
    public function if_it_has_trouble_updating_editions_config_it_should_instruct_user()
    {
        $this->files->put($this->editionsPath, <<<'EDITIONS'
<?php

return [

    'pro' => 'wabbajack', // It should fail trying to update this!

    'addons' => [
        //
    ],

];
EDITIONS);

        $this->assertFalse(Statamic::pro());
        $this->assertEquals($this->defaultEnvContents, $this->files->get($this->envPath));

        $this
            ->artisan('statamic:pro:enable', ['--update-config' => true])
            ->expectsOutput(PHP_EOL.'For this setting to take effect, please modify your [config/statamic/editions.php] as follows:')
            ->expectsOutput("'pro' => env('STATAMIC_PRO_ENABLED', false)");

        // Though it should still update .env
        $this->assertEquals(<<<'ENV'
APP_NAME=Statamic
STATAMIC_PRO_ENABLED=true
STATAMIC_LICENSE_KEY=
ENV, $this->files->get($this->envPath));

        // Pro should not be enabled in the in-memory config, because config is not properly referencing .env var yet
        $this->assertFalse(Statamic::pro());
    }
}
