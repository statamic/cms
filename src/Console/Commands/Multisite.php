<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Facades\File;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Statamic;
use Symfony\Component\VarExporter\VarExporter;

class Multisite extends Command
{
    use RunsInPlease, EnhancesCommands;

    protected $name = 'statamic:multisite';
    protected $description = 'Converts from a single to multisite installation.';

    protected $siteOne;
    protected $siteTwo;
    protected $siteTwoConfig;

    public function handle()
    {
        if (! $this->checkForAndEnablePro()) {
            return $this->crossLine('Multisite has not been enabled.');
        }

        Stache::disableUpdatingIndexes();

        $this->siteOne = Site::default()->handle();

        $this->validateRunningOfCommand();

        $confirmed = $this->confirm("The current site handle is [<comment>{$this->siteOne}</comment>], content will be moved into folders with this name. Is this okay?");

        if (! $confirmed) {
            $this->crossLine('Change the site handle in <comment>config/statamic/sites.php</comment> then try this command again.');

            return;
        }

        $this->siteTwo = $this->ask('Handle of the second site', 'two');

        $this->clearStache();

        $config = $this->updateSiteConfig();

        $this->comment('Converting...');

        Collection::all()->each(function ($collection) {
            $this->moveCollectionContent($collection);
            $this->updateCollection($collection);
            $this->checkLine("Collection [<comment>{$collection->handle()}</comment>] updated.");
        });

        GlobalSet::all()->each(function ($set) {
            $this->moveGlobalSet($set);
            $this->checkLine("Global [<comment>{$set->handle()}</comment>] updated.");
        });

        Nav::all()->each(function ($nav) {
            $this->moveNav($nav);
            $this->checkLine("Nav [<comment>{$nav->handle()}</comment>] updated.");
        });

        Cache::clear();
        $this->checkLine('Cache cleared.');

        $this->attemptToWriteSiteConfig($config);

        $this->checkInfo('Done!');
    }

    protected function clearStache()
    {
        Stache::clear();
        $this->checkLine('Stache cleared.');
        $this->line('');
    }

    protected function updateSiteConfig()
    {
        $sites = config('statamic.sites.sites');

        $this->siteTwoConfig = [
            'name' => $this->siteTwo,
            'locale' => 'en_US',
            'url' => "/{$this->siteTwo}/",
        ];

        $sites[$this->siteTwo] = $this->siteTwoConfig;

        Site::setConfig('sites', $sites);

        Stache::sites(Site::all()->map->handle());

        return $sites;
    }

    protected function attemptToWriteSiteConfig($config)
    {
        try {
            $this->writeSiteConfig($config);
        } catch (\Exception $e) {
            $this->error('Could not automatically update the sites config file.');
            $this->comment('[!] Update <comment>config/statamic/sites.php</comment>\'s "sites" array to the following:');
            $this->line(VarExporter::export($config));
        }
    }

    protected function writeSiteConfig($config)
    {
        $contents = File::get($path = config_path('statamic/sites.php'));

        // Create the php that should be added to the config file. Add the appropriate indentation.
        $newConfig = '\''.$this->siteTwo.'\' => '.VarExporter::export($this->siteTwoConfig);
        $newConfig = collect(explode("\n", $newConfig))->map(function ($line) {
            return '        '.$line;
        })->join("\n").',';

        // Use the closing square brace of the first site as the hook for injecting the second.
        // We'll assume the indentation is what you'd get on a fresh Statamic installation.
        // Otherwise, it'll likely break. The exception in the next step will handle it.
        $find = '        ],';
        $contents = preg_replace('/'.$find.'/', $find."\n\n".$newConfig, $contents);

        // Check that the new contents would be the same as what the config would be.
        // If not, fail and we'll give the user instructions on how to do it manually.
        $evaluated = eval(str_replace('<?php', '', $contents));
        $expected = ['sites' => $config];
        throw_if($evaluated !== $expected, new \Exception('The config could not be written.'));

        File::put($path, $contents);
        $this->checkLine('Site config file updated.');
    }

    protected function moveCollectionContent($collection)
    {
        $handle = $collection->handle();
        $base = "content/collections/{$handle}";

        File::makeDirectory("{$base}/{$this->siteOne}");

        File::getFiles($base)->each(function ($file) use ($base) {
            $filename = pathinfo($file, PATHINFO_BASENAME);
            File::move($file, "{$base}/{$this->siteOne}/{$filename}");
        });
    }

    protected function updateCollection($collection)
    {
        $collection->sites([$this->siteOne, $this->siteTwo]);

        if ($structure = $collection->structureContents()) {
            $tree = $structure['tree'];
            $structure['tree'] = [$this->siteOne => $tree];
            $collection->structureContents($structure);
        }

        $collection->save();
    }

    protected function moveGlobalSet($set)
    {
        $yaml = YAML::file($set->path())->parse();
        $data = $yaml['data'] ?? [];

        $set
            ->addLocalization($origin = $set->makeLocalization($this->siteOne)->data($data))
            ->addLocalization($set->makeLocalization($this->siteTwo)->origin($origin))
            ->save();
    }

    protected function moveNav($nav)
    {
        $yaml = YAML::file($nav->path())->parse();
        $tree = $yaml['tree'] ?? [];

        $nav
            ->addTree($nav->makeTree($this->siteOne)->tree($tree))
            ->addTree($nav->makeTree($this->siteTwo)->tree($tree))
            ->save();
    }

    protected function validateRunningOfCommand()
    {
        if (Site::hasMultiple()) {
            exit($this->error('Already configured for multi-site.'));
        }

        if ($this->commandMayHaveBeenRan()) {
            exit($this->error('Command may have already been run. Did you update your config/statamic/sites.php file?'));
        }
    }

    protected function commandMayHaveBeenRan()
    {
        return $this->collectionsHaveBeenMoved()
            || $this->globalsHaveBeenMoved()
            || $this->navsHaveBeenMoved();
    }

    protected function collectionsHaveBeenMoved()
    {
        if (! $collection = Collection::all()->first()) {
            return false;
        }

        return File::isDirectory("content/collections/{$collection->handle()}/{$this->siteOne}");
    }

    protected function globalsHaveBeenMoved()
    {
        return File::isDirectory("content/globals/{$this->siteOne}");
    }

    protected function navsHaveBeenMoved()
    {
        return File::isDirectory("content/navigation/{$this->siteOne}");
    }

    protected function checkForAndEnablePro()
    {
        if (Statamic::pro()) {
            return true;
        }

        if (! $this->confirm('Statamic Pro is required for multiple sites. Would you like to enable it?', true)) {
            return false;
        }

        try {
            Statamic::enablePro();
            $this->checkLine('Statamic Pro has been enabled.');
        } catch (\Exception $e) {
            $this->error('Could not automatically enable Pro.');
            $this->line('You can enable it manually in <comment>config/statamic/editions.php</comment>');

            return false;
        }

        return true;
    }
}
