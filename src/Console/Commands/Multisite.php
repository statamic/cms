<?php

namespace Statamic\Console\Commands;

use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Facades\Stache;
use Illuminate\Console\Command;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Structure;
use Statamic\Facades\Collection;
use Statamic\Console\RunsInPlease;
use Illuminate\Support\Facades\Cache;
use Statamic\Console\EnhancesCommands;
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
            $this->addSitesToCollection($collection);
            $this->checkLine("Collection [<comment>{$collection->handle()}</comment>] updated.");
        });

        GlobalSet::all()->each(function ($set) {
            $this->moveGlobalSet($set);
            $this->checkLine("Global [<comment>{$set->handle()}</comment>] updated.");
        });

        Structure::all()->each(function ($structure) {
            $this->moveStructure($structure);
            $this->checkLine("Structure [<comment>{$structure->handle()}</comment>] updated.");
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

    protected function addSitesToCollection($collection)
    {
        $collection->sites([$this->siteOne, $this->siteTwo])->save();
    }

    protected function moveGlobalSet($set)
    {
        $yaml = YAML::file($set->path())->parse();
        $data = $yaml['data'] ?? [];

        $set
            ->sites([$this->siteOne, $this->siteTwo])
            ->addLocalization($origin = $set->makeLocalization($this->siteOne)->data($data))
            ->addLocalization($set->makeLocalization($this->siteTwo)->origin($origin))
            ->save();
    }

    protected function moveStructure($structure)
    {
        $yaml = YAML::file($structure->path())->parse();
        $tree = $yaml['tree'] ?? [];
        $root = $yaml['root'] ?? null;

        $structure
            ->sites([$this->siteOne, $this->siteTwo])
            ->addTree($structure->makeTree($this->siteOne)->tree($tree)->root($root))
            ->addTree($structure->makeTree($this->siteTwo)->tree($tree)->root($root))
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
            || $this->structuresHaveBeenMoved();
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

    protected function structuresHaveBeenMoved()
    {
        return File::isDirectory("content/structures/{$this->siteOne}");
    }
}
