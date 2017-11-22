<?php

namespace Statamic\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use Statamic\API\Config;
use Illuminate\Http\Request;
use Statamic\Updater\Updater;
use Statamic\Updater\ZipDownloadedException;

class UpdaterController extends CpController
{
    /**
     * @var Updater
     */
    private $updater;

    public function __construct(Updater $updater)
    {
        parent::__construct(request());

        $this->updater = $updater;
    }

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

        return view('updater.index', [
            'title' => 'Updater',
            'releases' => $releases,
            'latest' => $releases[0]
        ]);
    }

    /**
     * Show and confirm the specific update
     *
     * @param string $version  The version number
     * @return \Illuminate\View\View
     */
    public function confirmUpdate($version)
    {
        $this->access('updater:update');

        $title = version_compare($version, STATAMIC_VERSION, '>') ? "Upgrade" : "Downgrade";

        return view('updater.confirm', [
            'title' => $title,
            'version' => $version,
            'license_key' => Config::get('system.license_key', false)
        ]);
    }

    /**
     * Create a backup
     *
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function backup()
    {
        $this->authorize('updater:update');

        $this->updater->backup();
    }

    /**
     * Download Statamic
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function download(Request $request)
    {
        $this->authorize('updater:update');

        try {
            $this->updater->setVersion($request->version)->download();
        } catch (ZipDownloadedException $e) {
            return $this->okay(sprintf(
                'Download skipped. Using previously downloaded zip detected at <code>%s</code>.',
                $e->getZipPath()
            ));
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), $e);
        }
    }

    /**
     * Unzip the Statamic download
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unzip(Request $request)
    {
        $this->authorize('updater:update');

        try {
            $this->updater->setVersion($request->version)->extract();
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), $e);
        }
    }

    /**
     * Install composer dependencies
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function composer()
    {
        $this->authorize('updater:update');

        try {
            $this->updater->updateDependencies();
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), $e->getPrevious());
        }
    }

    /**
     * Swap the statamic folder
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function swap()
    {
        $this->authorize('updater:update');

        try {
            $this->updater->swapFiles();
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), $e);
        }
    }

    /**
     * Clean up
     *
     * This step is performed _after_ the update is completed.
     * It will be performed on the new version's code.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cleanUp(Request $request)
    {
        try {
            $this->updater
                 ->setVersion($request->version)
                 ->setPreviousVersion($request->oldVersion)
                 ->cleanUp();
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), $e);
        }
    }

    /**
     * Generate a success response
     *
     * @param string $message
     * @return array
     */
    private function okay($message)
    {
        return ['success' => true, 'message' => $message];
    }

    /**
     * Generate a failure response
     *
     * @param string|array    $error  Either a message, or an array of messages and exceptions.
     * @param Exception|null $e      An exception when passing a single error message.
     * @return \Illuminate\Http\JsonResponse
     */
    private function fail($data, $e = null)
    {
        $errors = [];

        if (is_string($data)) {
            $data = [
                ['message' => $data, 'e' => $e]
            ];
        }

        foreach ($data as $error) {
            $message = $error['message'];
            $e = ($error['e'] instanceof Exception) ? $error['e']->getMessage() : null;

            $errors[] = compact('message', 'e');
        }

        return response()->json([
            'success' => false,
            'errors'  => $errors
        ], 500);
    }
}
