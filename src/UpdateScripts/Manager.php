<?php

namespace Statamic\UpdateScripts;

use Statamic\Console\Composer\Lock;
use Statamic\Exceptions\ComposerLockFileNotFoundException;
use Statamic\Exceptions\ComposerLockPackageNotFoundException;

class Manager
{
    /**
     * Run all registered update scripts.
     *
     * @param  mixed  $console
     * @return bool
     */
    public function runAll($console = null)
    {
        $newLockFile = Lock::file();
        $oldLockFile = Lock::file(UpdateScript::BACKUP_PATH);

        $scripts = $this->runUpdatableScripts(
            $this->getRegisteredScripts($console),
            $oldLockFile,
            $newLockFile
        );

        $oldLockFile->delete();

        return $scripts->isNotEmpty();
    }

    /**
     * Run all registered update scripts for a specific package version.
     *
     * @param  string  $package
     * @param  string  $oldVersion
     * @param  mixed  $console
     * @return bool
     */
    public function runUpdatesForSpecificPackageVersion($package, $oldVersion, $console = null)
    {
        Lock::backup(base_path('composer.lock'));

        $newLockFile = Lock::file();
        $oldLockFile = Lock::file(UpdateScript::BACKUP_PATH)->overridePackageVersion($package, $oldVersion);

        $scripts = $this->getRegisteredScripts($console)->filter(function ($script) use ($package) {
            return $script->package() === $package;
        });

        $scripts = $this->runUpdatableScripts($scripts, $oldLockFile, $newLockFile);

        $oldLockFile->delete();

        return $scripts->isNotEmpty();
    }

    /**
     * Get registered update scripts.
     *
     * @param  mixed  $console
     * @return \Illuminate\Support\Collection
     */
    protected function getRegisteredScripts($console)
    {
        return app('statamic.update-scripts')->map(function ($script) use ($console) {
            return new $script['class']($script['package'], $console);
        });
    }

    /**
     * Run updatable scripts.
     *
     * @param  \Illuminate\Support\Collection  $scripts
     * @param  Lock  $oldLockFile
     * @param  Lock  $newLockFile
     * @return \Illuminate\Support\Collection
     */
    protected function runUpdatableScripts($scripts, $oldLockFile, $newLockFile)
    {
        return $scripts
            ->filter(function ($script) use ($newLockFile, $oldLockFile) {
                try {
                    return $script->shouldUpdate(
                        $newLockFile->getNormalizedInstalledVersion($script->package()),
                        $oldLockFile->getNormalizedInstalledVersion($script->package())
                    );
                } catch (ComposerLockFileNotFoundException|ComposerLockPackageNotFoundException $exception) {
                    return false;
                }
            })
            ->each(function ($script) {
                $script->console()->info('Running update script <comment>['.get_class($script).']</comment>');
                $script->update();
            });
    }
}
