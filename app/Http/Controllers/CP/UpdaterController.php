<?php

namespace Statamic\Http\Controllers\CP;

use GuzzleHttp\Client;

class UpdaterController extends CpController
{
    /**
     * Show the available updates and changelogs
     *
     * @return \Illuminate\View\View
     */
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

    /**
     * Show update instructions
     *
     * @param string $version  The version number
     * @return \Illuminate\View\View
     */
    public function update($version)
    {
        $this->access('updater:update');

        return view('statamic::updater.update', [
            'title' => 'Update',
            'version' => $version,
        ]);
    }
}
