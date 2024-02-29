<?php

namespace Tests\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InstallSsgTest extends TestCase
{
    /** @test */
    public function it_can_install_the_ssg_package()
    {
        Composer::shouldReceive('isInstalled')
            ->with('statamic/ssg')
            ->andReturnFalse();

        Composer::shouldReceive('withoutQueue')
            ->andReturnSelf()
            ->shouldReceive('throwOnFailure')
            ->andReturnSelf()
            ->shouldReceive('require')
            ->with('statamic/ssg')
            ->andReturnSelf();

        File::shouldReceive('copy', base_path('vendor/statamic/ssg/config/ssg.php'), config_path('statamic/ssg.php'))
            ->andReturnSelf();

        Composer::shouldReceive('isInstalled')
            ->with('spatie/fork')
            ->andReturnFalse();

        Composer::shouldReceive('withoutQueue')
            ->andReturnSelf()
            ->shouldReceive('throwOnFailure')
            ->andReturnSelf()
            ->shouldReceive('require')
            ->with('spatie/fork')
            ->andReturnSelf();

        $this
            ->artisan('statamic:install:ssg')
            ->expectsOutput('Installing the statamic/ssg package...')
            ->expectsOutputToContain('Installed statamic/ssg package')
            ->expectsConfirmation('Would you like to publish the config file?', true)
            ->expectsConfirmation('Would you like to install spatie/fork? It allows for running multiple workers at once.', true);
    }

    /** @test */
    public function it_does_not_ask_the_user_about_spatie_fork_when_it_is_already_installed()
    {
        Composer::shouldReceive('isInstalled')
            ->with('statamic/ssg')
            ->andReturnFalse();

        Composer::shouldReceive('withoutQueue')
            ->andReturnSelf()
            ->shouldReceive('throwOnFailure')
            ->andReturnSelf()
            ->shouldReceive('require')
            ->with('statamic/ssg')
            ->andReturnSelf();

        File::shouldReceive('copy', base_path('vendor/statamic/ssg/config/ssg.php'), config_path('statamic/ssg.php'))
            ->andReturnSelf();

        Composer::shouldReceive('isInstalled')
            ->with('spatie/fork')
            ->andReturnTrue();

        $this
            ->artisan('statamic:install:ssg')
            ->expectsOutput('Installing the statamic/ssg package...')
            ->expectsOutputToContain('Installed statamic/ssg package')
            ->expectsConfirmation('Would you like to publish the config file?', true);
    }

    /** @test */
    public function it_cant_install_the_ssg_package_when_it_is_already_installed()
    {
        Composer::shouldReceive('isInstalled')
            ->with('statamic/ssg')
            ->andReturnTrue();

        $this
            ->artisan('statamic:install:ssg')
            ->expectsOutput('The Static Site Generator package is already installed.');
    }
}
