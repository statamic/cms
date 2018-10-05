<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\Composer\Composer;
use Facades\Statamic\Composer\CoreChangelog;
use Facades\Statamic\Composer\CoreUpdater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UpdaterController extends CpController
{
    const CORE = 'test/package';

    public function __construct()
    {
        // All temporary stuff to get this all hooked up with test/package instead of statamic/cms.
        $fakeCoreUpdater = new \Statamic\Composer\CoreUpdater;
        $fakeCoreUpdater->core = self::CORE;
        CoreUpdater::swap($fakeCoreUpdater);
        require(base_path('vendor/statamic/cms/tests/Fakes/Composer/Package/PackToTheFuture.php'));
    }

    public function index()
    {
        return view('statamic::updater.index', [
            'title' => 'Updates'
        ]);
    }

    public function version()
    {
        return [
            'currentVersion' => Composer::installed()->get(self::CORE)->version,
            'lastInstallLog' => Composer::lastCachedOutput(self::CORE),
        ];
    }

    public function changelog()
    {
        return CoreChangelog::get();
    }

    public function update()
    {
        return CoreUpdater::update();
    }

    public function updateToLatest()
    {
        \Tests\Fakes\Composer\Package\PackToTheFuture::setVersion(CoreUpdater::latestVersion()); // Temp!

        return CoreUpdater::updateToLatest();
    }

    public function installExplicitVersion(Request $request)
    {
        \Tests\Fakes\Composer\Package\PackToTheFuture::setVersion($request->version); // Temp!

        return CoreUpdater::installExplicitVersion($request->version);
    }

    // /**
    //  * Show the available updates and changelogs
    //  *
    //  * @return \Illuminate\View\View
    //  */
    // public function index()
    // {
    //     $this->access('updater');

    //     $client = new \GuzzleHttp\Client\Client();
    //     $response = $client->get('https://outpost.statamic.com/v2/changelog');
    //     $releases = json_decode($response->getBody());

    //     return view('statamic::updater.index', [
    //         'title' => 'Updater',
    //         'releases' => $releases,
    //         'latest' => $releases[0]
    //     ]);
    // }

    // /**
    //  * Show update instructions
    //  *
    //  * @param string $version  The version number
    //  * @return \Illuminate\View\View
    //  */
    // public function update($version)
    // {
    //     $this->access('updater:update');

    //     return view('statamic::updater.update', [
    //         'title' => 'Update',
    //         'version' => $version,
    //     ]);
    // }
}
