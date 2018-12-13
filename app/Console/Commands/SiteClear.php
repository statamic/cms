<?php

namespace Statamic\Console\Commands;

use Statamic\API\File;
use Statamic\API\Config;
use Statamic\API\Folder;
use Illuminate\Console\Command;
use Statamic\Console\Commands\Traits\RunsInPlease;

class SiteClear extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:site:clear
                            {--force : Skip the confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a fresh site, wiping away all content';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // TODO: Not sure what we're doing with Redwood yet?

        // $this->clearContent();
        // $this->clearStorage();
        // $this->clearSettings();
        // $this->clearThemes();
        // $this->clearAssets();
        // $this->clearUsers();
        //
        // $this->info('Site cleared!');
    }

    /**
     * Clear the content
     *
     * @return void
     */
    private function clearContent()
    {
        if (! $this->confirmSection('content')) {
            return;
        }

        foreach (['collections', 'taxonomies', 'globals', 'pages'] as $type) {
            Folder::disk('content')->delete($type);
            Folder::disk('content')->make($type);
        }

        $this->line('Content cleared.');

        $this->createHomePage();
    }

    /**
     * Create a home page
     *
     * @return void
     */
    private function createHomePage()
    {
        $path = 'pages/index.' . Config::get('system.default_extension');

        $contents = 'title: ' . trans('cp.home');

        File::disk('content')->put($path, $contents);

        $this->line('Home page created.');
    }

    /**
     * Clear the storage
     *
     * @return void
     */
    private function clearStorage()
    {
        if (! $this->confirmSection('storage')) {
            return;
        }

        foreach (Folder::disk('storage')->getFilesRecursively('/') as $file) {
            File::disk('storage')->delete($file);
        }

        Folder::disk('storage')->deleteEmptySubfolders('/');

        $this->line('Storage cleared.');
    }

    /**
     * Clear settings
     *
     * @return void
     */
    private function clearSettings()
    {
        if (! $this->confirmSection('settings')) {
            return;
        }

        foreach (['addons', 'environments', 'fieldsets', 'formsets'] as $thing) {
            Folder::delete('site/settings/'.$thing);
            Folder::make('site/settings/'.$thing);
        }

        $files = [
            'assets' => '',
            'caching' => '',
            'cp' => '',
            'debug' => '',
            'email' => '',
            'routes' => '',
            'search' => '',
            'system' => 'yaml_parser: symfony',
            'theming' => '',
            'users' => '',
            'users/groups' => '',
            'users/roles' => '',
        ];

        foreach ($files as $file => $contents) {
            File::put('site/settings/'.$file.'.yaml', $contents);
        }

        $this->line('Settings cleared.');
    }

    /**
     * Clear themes
     *
     * @return void
     */
    private function clearThemes()
    {
        if (! $this->confirmSection('themes')) {
            return;
        }

        foreach (Folder::disk('themes')->getFilesRecursively('/') as $file) {
            File::disk('themes')->delete($file);
        }

        Folder::disk('themes')->deleteEmptySubfolders('/');

        $this->line('Themes cleared. You may want to consider creating one using `php please make:theme`');
    }

    /**
     * Clear users
     *
     * @return void
     */
    private function clearAssets()
    {
        if (! $this->confirmSection('assets')) {
            return;
        }

        foreach (Folder::getFiles('assets') as $file) {
            File::delete($file);
        }

        foreach (Folder::getFiles('assets/img') as $file) {
            File::delete($file);
        }

        Folder::delete('assets/img');

        $this->line('Assets cleared.');
    }

    /**
     * Clear assets
     *
     * @return void
     */
    private function clearUsers()
    {
        if (! $this->confirmSection('users')) {
            return;
        }

        foreach (Folder::getFiles('site/users') as $file) {
            File::delete($file);
        }

        $this->line('Users cleared.');
    }

    /**
     * Confirm whether the user wants a section cleared
     *
     * @param  string $section
     * @return boolean
     */
    private function confirmSection($section)
    {
        if ($this->option('force')) {
            return true;
        }

        return $this->confirm("Clear {$section}?");
    }
}
