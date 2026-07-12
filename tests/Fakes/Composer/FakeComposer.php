<?php

namespace Tests\Fakes\Composer;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\Blink;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FakeComposer
{
    private $files;

    public function __construct()
    {
        $this->files = app(Filesystem::class);
    }

    public function require($package, $version = null, ...$extraParams)
    {
        [$package, $branch] = $this->parseRawPackageArg($package);

        Blink::put('composer-require-package', $package);
        Blink::put('composer-require-branch', $branch);

        if (collect($extraParams)->contains('--dry-run')) {
            return;
        }

        $this->fakeInstallComposerJson('require', $package, $version);
        $this->fakeInstallVendorFiles($package);
    }

    public function requireDev($package, $version = null, ...$extraParams)
    {
        [$package, $branch] = $this->parseRawPackageArg($package);

        Blink::put('composer-require-dev-package', $package);
        Blink::put('composer-require-dev-branch', $branch);

        if (collect($extraParams)->contains('--dry-run')) {
            return;
        }

        $this->fakeInstallComposerJson('require-dev', $package, $version);
        $this->fakeInstallVendorFiles($package);
    }

    public function requireMultiple($packages, ...$extraParams)
    {
        foreach ($packages as $package => $version) {
            $this->require($package, $version, ...$extraParams);
        }
    }

    public function requireMultipleDev($packages, ...$extraParams)
    {
        foreach ($packages as $package => $version) {
            $this->requireDev($package, $version, ...$extraParams);
        }
    }

    public function remove($package)
    {
        $this->removeFromComposerJson($package);
        $this->removeFromVendorFiles($package);
    }

    public function removeDev($package)
    {
        $this->remove($package);
    }

    public function runAndOperateOnOutput($args, $callback)
    {
        $args = collect($args);

        if (! $args->contains('require')) {
            return;
        }

        $requireMethod = $args->contains('--dev')
            ? 'requireMultipleDev'
            : 'requireMultiple';

        $packages = $args
            ->filter(function ($arg) {
                return Str::contains($arg, '/');
            })
            ->mapWithKeys(function ($arg) {
                $parts = explode(':', $arg);

                return [$parts[0] => $parts[1]];
            })
            ->all();

        $this->{$requireMethod}($packages);
    }

    private function fakeInstallComposerJson($requireKey, $package, $version)
    {
        $composerJson = json_decode($this->files->get(base_path('composer.json')), true);

        $composerJson[$requireKey][$package] = $version ?? '*';

        $this->files->put(
            base_path('composer.json'),
            json_encode($composerJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );
    }

    private function removeFromComposerJson($package)
    {
        $composerJson = json_decode($this->files->get(base_path('composer.json')), true);

        Arr::forget($composerJson, "require.{$package}");
        Arr::forget($composerJson, "require-dev.{$package}");

        $this->files->put(
            base_path('composer.json'),
            json_encode($composerJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );
    }

    private function fakeInstallVendorFiles($package)
    {
        if ($this->files->exists($path = base_path("vendor/{$package}"))) {
            $this->files->deleteDirectory($path);
        }

        if ($package === 'statamic/cool-runnings') {
            $this->files->copyDirectory(base_path('repo/cool-runnings'), $path);
        } else {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    private function removeFromVendorFiles($package)
    {
        if ($this->files->exists($path = base_path("vendor/{$package}"))) {
            $this->files->deleteDirectory($path);
        }
    }

    protected function parseRawPackageArg(string $package): array
    {
        $parts = explode(':', $package);

        if (count($parts) === 1) {
            $parts[] = null;
        }

        return $parts;
    }

    public function __call($method, $args)
    {
        return $this;
    }
}
