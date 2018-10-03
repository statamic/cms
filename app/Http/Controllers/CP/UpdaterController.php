<?php

namespace Statamic\Http\Controllers\CP;

use GuzzleHttp\Client;
use Facades\Statamic\Composer\CoreChangelog;
use Facades\Statamic\Composer\CoreUpdater;
use Illuminate\Http\Request;
use Tests\Fakes\Composer\Package\PackToTheFuture;

class UpdaterController extends CpController
{
    public function __construct()
    {
        $fakeCoreUpdater = new \Statamic\Composer\CoreUpdater;
        $fakeCoreUpdater->core = 'test/package';
        CoreUpdater::swap($fakeCoreUpdater);
    }

    // public function index()
    // {
    //     return view('statamic::updater.index', [
    //         'title' => 'Addons'
    //     ]);
    // }

    public function version()
    {
        // Temp!
        try {
            return \Facades\Statamic\Composer\Composer::installed()->get('test/package')->version;
        } catch (\Exception $exception) {
            return 'n/a';
        }
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
        PackToTheFuture::setVersion(CoreUpdater::latestVersion()); // Temp!

        return CoreUpdater::updateToLatest();
    }

    public function installExplicitVersion(Request $request)
    {
        PackToTheFuture::setVersion($request->version); // Temp!

        return CoreUpdater::installExplicitVersion($request->version);
    }

    // /**
    //  * Show the available updates and changelogs
    //  *
    //  * @return \Illuminate\View\View
    //  */
    public function index()
    {
        $this->access('updater');

        $client = new Client();
        $response = $client->get('https://outpost.statamic.com/v2/changelog');
        $releases = json_decode($response->getBody());

        return view('statamic::updater.index', [
            'title' => 'Updater',
            'releases' => $releases,
            'latest' => $releases[0]
        ]);
    }

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
