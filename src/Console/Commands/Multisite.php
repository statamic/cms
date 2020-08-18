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
use Symfony\Component\VarExporter\VarExporter;

class Multisite extends Command
{
    use RunsInPlease, EnhancesCommands;

    protected $name = 'statamic:multisite';
    protected $description = 'Converts from a single to multisite installation.';

    protected $siteOne;
    protected $siteTwo;

    public function handle()
    {
        Stache::disableUpdatingIndexes();

        $this->siteOne = Site::default()->handle();

        $this->validateRunningOfCommand();

        $confirmed = $this->confirm("The current site handle is [<comment>{$this->siteOne}</comment>], content will be moved into folders with this name. Is this okay?");

        if (! $confirmed) {
            $this->crossLine('Change the site handle in <comment>config/statamic/sites.php</comment> then try this command again.');

            return;
        }

        $this->siteTwo = $this->ask('Handle of the second site', 'two');

        $config = $this->updateSiteConfig();

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

        $this->checkInfo('Done!');

        $this->comment('[!] Update <comment>config/statamic/sites.php</comment>\'s sites array to the following:');
        $this->line(VarExporter::export($config));
    }

    protected function updateSiteConfig()
    {
        $sites = config('statamic.sites.sites');

        $sites[$this->siteTwo] = [
            'name' => $this->siteTwo,
            'locale' => 'en_US',
            'url' => "/{$this->siteTwo}/",
        ];

        Site::setConfig('sites', $sites);

        Stache::sites(Site::all()->map->handle());

        return $sites;
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
}
