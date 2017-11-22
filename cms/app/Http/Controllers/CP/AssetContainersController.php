<?php

namespace Statamic\Http\Controllers;

use Aws\S3\Exception\PermanentRedirectException;
use Aws\S3\Exception\S3Exception;
use Illuminate\Http\Request;
use Statamic\API\Parse;
use Statamic\Assets\AssetContainerManager;
use Statamic\Http\Requests;
use Statamic\API\AssetContainer;
use Statamic\API\Stache;
use Statamic\API\User;
use Statamic\API\Helper;

class AssetContainersController extends CpController
{
    public function manage()
    {
        return view('assets.containers.manage', [
            'title' => 'Assets'
        ]);
    }

    public function get()
    {
        $containers = [];

        foreach (AssetContainer::all() as $container) {
            if (! User::getCurrent()->can("assets:{$container->uuid()}:edit")) {
                continue;
            }

            $containers[] = [
                'id' => $container->uuid(),
                'title' => $container->title(),
                'assets' => $container->assets()->count(),
                'edit_url'    => $container->editUrl(),
                'browse_url' => route('assets.browse', $container->uuid())
            ];
        }

        return ['columns' => ['title'], 'items' => $containers];
    }

    public function create()
    {
        return view('assets.containers.create', [
            'title' => 'Creating Asset Container'
        ]);
    }

    public function store(Requests\StoreAssetContainerRequest $request)
    {
        $container = AssetContainer::create();

        $container->handle($request->handle);

        return $this->save($container);
    }

    public function edit($uuid)
    {
        $container = AssetContainer::find($uuid);

        return view('assets.containers.edit', [
            'title'     => t('editing_asset_container'),
            'container' => $container
        ]);
    }

    public function update(Requests\UpdateAssetContainerRequest $request, $uuid)
    {
        $container = AssetContainer::find($uuid);

        return $this->save($container);
    }

    private function save($container)
    {
        $driver = $this->request->input('driver');

        $config = $this->request->input($driver);

        $data = [
            'title' => $this->request->input('title'),
            'fieldset' => $this->request->input('fieldset'),
        ];

        $data = array_merge($config, $container->data(), $data);

        $container->data($data);

        $container->driver($driver);

        $container->save();

        $this->success('Container saved');

        return [
            'success' => true,
            'redirect' => route('assets.container.edit', $container->uuid())
        ];
    }

    public function delete()
    {
        $ids = Helper::ensureArray($this->request->input('ids'));

        foreach ($ids as $id) {
            AssetContainer::find($id)->delete();
        }

        return ['success' => true];
    }

    public function folders($container)
    {
        $container = AssetContainer::find($container);

        $folders = collect([
            ['path' => '/', 'title' => '/']
        ]);

        $assetFolders = $container->assetFolders()->map(function ($folder) {
            return [
                'path' => $folder->path(),
                'title' => $folder->has('title') ? $folder->title() : $folder->path()
            ];
        })->values();

        return $folders->merge($assetFolders);
    }

    /**
     * Get the resolved path
     *
     * Used from the asset container wizard when typing a path.
     *
     * @param Request $request
     * @param AssetContainerManager $manager
     * @return array|null
     */
    public function getResolvedPath(Request $request, AssetContainerManager $manager)
    {
        $path = Parse::env($request->path);

        if (! $path || $path === '') {
            return null;
        }

        $resolved = $manager->resolveLocalPath($path);

        return [
            'path' => $resolved,
            'exists' => is_dir($resolved)
        ];
    }

    /**
     * Get the resolved URL
     *
     * Used from the asset container wizard when typing a URL.
     *
     * @param Request $request
     * @param AssetContainerManager $manager
     * @return array|null
     */
    public function getResolvedUrl(Request $request, AssetContainerManager $manager)
    {
        $url = $manager->getAbsoluteUrl(
            Parse::env($request->url)
        );

        return compact('url');
    }

    public function validateS3Credentials(Request $request, AssetContainerManager $manager)
    {
        try {
            $files = $manager->createS3Filesystem($request->all())->files('/');

            return [
                'success' => true,
                'files' => count($files)
            ];
        } catch (S3Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $this->parseS3ExceptionMessage($e)
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    private function parseS3ExceptionMessage(S3Exception $e)
    {
        if ($e instanceof PermanentRedirectException) {
            return $e->getMessage();
        }

        $xml = $e->getResponse()->getBody();

        preg_match('/<Code>(.*)<\/Code><Message>(.*)<\/Message>/', $xml, $matches);

        return sprintf('%s (Error Code: %s)', $matches[2], $matches[1]);
    }
}
