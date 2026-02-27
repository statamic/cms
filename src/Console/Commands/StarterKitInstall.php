<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Prompts\Prompt;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Rules\ComposerPackage;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\StarterKits\Installer as StarterKitInstaller;
use Statamic\StarterKits\LicenseManager as StarterKitLicenseManager;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class StarterKitInstall extends Command
{
    use RunsInPlease, ValidatesInput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:starter-kit:install
        { package? : Specify the starter kit package to install }
        { --license= : Provide explicit starter kit license key }
        { --local : Install from local repo configured in composer config.json }
        { --with-config : Copy starter-kit.yaml config for local development }
        { --without-dependencies : Install without dependencies }
        { --without-user : Install without creating user }
        { --force : Force install and allow dependency errors }
        { --cli-install : Installing from CLI Tool }
        { --clear-site : Clear site before installing }
        { --update-search : Update search index(es) after installing }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install starter kit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        [$package, $branch] = $this->getPackageAndBranch();

        if ($this->validationFails($package, new ComposerPackage)) {
            return 1;
        }

        $licenseManager = StarterKitLicenseManager::validate($package, $this->option('license'), $this, $this->input->isInteractive());

        if (! $licenseManager->isValid()) {
            return;
        }

        if ($cleared = $this->shouldClearSite()) {
            $this->clearSite();
        }

        $installer = (new StarterKitInstaller($package, $this, $licenseManager))
            ->branch($branch)
            ->fromLocalRepo($this->option('local'))
            ->withConfig($this->option('with-config'))
            ->withoutDependencies($this->option('without-dependencies'))
            ->withUserPrompt($cleared && $this->input->isInteractive() && ! $this->option('without-user') && ! $this->option('cli-install'))
            ->isInteractive($this->input->isInteractive())
            ->usingSubProcess($this->option('cli-install'))
            ->force($this->option('force'));

        try {
            $installer->install();
        } catch (StarterKitException $exception) {
            $this->components->error($exception->getMessage());

            return 1;
        }

        if ($this->shouldUpdateSearchIndex()) {
            $this->updateSearchIndex();
        }

        // Temporary prompt to inform user of updated CLI tool. The newest version has better messaging
        // around paid starter kit licenses, so we want to push users to upgrade to minimize support
        // requests around expired licenses. The newer version of the CLI tool will also notify
        // the user of older CLI tool versions going forward, so we can rip this out later.
        if ($this->oldCliToolInstallationDetected()) {
            $this->comment(PHP_EOL.'We have detected that you may be running an old version of the Statamic CLI Tool!');
            $this->comment('If you have a global composer installation, you may upgrade by running the following command:');
            $this->comment('composer global update statamic/cli'.PHP_EOL);
        }

        if (version_compare(app()->version(), '11', '<')) {
            return $this->components->info("Starter kit [$package] was successfully installed.");
        }

        $this->components->success("Starter kit [$package] was successfully installed.");
    }

    /**
     * Get composer package (and optional branch).
     */
    protected function getPackageAndBranch(): array
    {
        $package = $this->argument('package') ?: text('Package');

        $parts = explode(':', $package);

        if (count($parts) === 1) {
            $parts[] = null;
        }

        return $parts;
    }

    /**
     * Check if should clear site first.
     */
    protected function shouldClearSite(): bool
    {
        if ($this->option('clear-site')) {
            return true;
        } elseif ($this->input->isInteractive()) {
            return confirm('Clear site first?', false);
        }

        return false;
    }

    /**
     * Clear site, and re-set prompt interactivity for future prompts.
     *
     * See: https://github.com/statamic/cli/issues/62
     */
    protected function clearSite(): void
    {
        $this->call('statamic:site:clear', ['--no-interaction' => true]);

        Prompt::interactive($this->input->isInteractive());
    }

    /**
     * Check if should update search index.
     */
    protected function shouldUpdateSearchIndex(): bool
    {
        if ($this->option('update-search')) {
            return true;
        } elseif ($this->input->isInteractive()) {
            return confirm('Would you like to update your search index(es) as well?', false);
        }

        return false;
    }

    /**
     * Update search index, and re-set prompt interactivity for future prompts.
     *
     * See: https://github.com/statamic/cli/issues/62
     */
    protected function updateSearchIndex(): void
    {
        $this->call('statamic:search:update', [
            '--all' => true,
            '--no-interaction' => true,
        ]);

        Prompt::interactive($this->input->isInteractive());
    }

    /**
     * Detect older Statamic CLI installation.
     */
    private function oldCliToolInstallationDetected(): bool
    {
        return (! $this->input->isInteractive()) // CLI tool never runs interactively.
            && (! $this->option('cli-install'))  // Updated CLI tool passes this option.
            && $this->option('clear-site');      // CLI tool always clears site.
    }
}
