<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\View\Cascade;

class NoCacheManager
{
    /**
     * @var Collection
     */
    private $config;

    /**
     * The nocache cache directory.
     *
     * @var string
     */
    private $cacheDirectory = '';

    /**
     * The active CacheSession instance.
     *
     * @var CacheSession|null
     */
    private $cacheSession = null;

    /**
     * The Parser implementation instance.
     *
     * @var Parser
     */
    private $parser;

    private $files;

    /**
     * The Cascade instance.
     *
     * @var Cascade
     */
    private $cascade;

    /**
     * Indicates if the current request is being restored from the no_cache cache.
     *
     * @var bool
     */
    public static $isRehydrated = false;

    public function __construct($config, $cacheDirectory, Parser $parser, Cascade $cascade, Filesystem $files)
    {
        $this->files = $files;
        $this->config = $config;
        $this->cacheDirectory = $cacheDirectory;
        $this->parser = $parser;
        $this->cascade = $cascade;
    }

    /**
     * Resets the shared no_cache state.
     */
    public static function reset()
    {
        self::$isRehydrated = false;
        NoCache::$stack = 0;
    }

    /**
     * Constructs a cache key for the request URL.
     *
     * @param  string  $url  The request URL.
     * @return string
     */
    private function makeKey($url)
    {
        return '__nocache_'.md5($url);
    }

    /**
     * Returns access to the current cache session.
     *
     * @return CacheSession
     */
    public function session()
    {
        if ($this->cacheSession == null) {
            $this->cacheSession = new CacheSession();
        }

        return $this->cacheSession;
    }

    public function flush()
    {
        $this->files->deleteDirectory($this->cacheDirectory);
    }

    public function invalidateUrl($url)
    {
        $cacheDirectory = $this->getCacheDirectory($url);

        if ($this->files->exists($cacheDirectory)) {
            $this->files->deleteDirectory($cacheDirectory);
        }
    }

    /**
     * Tests if the NoCacheManager can handle the current request.
     *
     * @param  Request  $request  The request.
     * @return bool
     */
    public function canHandle(Request $request)
    {
        return $this->cacheFileExists($this->getUrl($request));
    }

    /**
     * Checks if a cached file exists for the provided URL.
     *
     * @param  string  $url  The request URL.
     * @return bool
     */
    private function cacheFileExists($url)
    {
        $targetCacheManifest = $this->makeKey($url).'/.nocache.manifest';
        $targetFile = $this->cacheDirectory.$targetCacheManifest;

        return $this->files->exists($targetFile);
    }

    /**
     * Constructs a dynamic Antlers tag to be evaluated later.
     *
     * @param  string  $regionName  The nocache region name.
     * @return string
     */
    private function makeEvaluateTag($regionName)
    {
        return '{{ no_cache:evaluate region="'.substr($regionName, 19).'" }}';
    }

    private function getCacheDirectory($url)
    {
        return $this->cacheDirectory.$this->makeKey($url);
    }

    /**
     * Writes the active nocache session to the cache.
     *
     * @param  Request  $request  The request.
     * @param  string  $contents  The rewritten template contents.
     */
    public function writeSession(Request $request, $contents)
    {
        $sections = $this->session()->getSections();
        $contexts = $this->session()->getContexts();

        $url = $this->getUrl($request);

        $cacheDirectory = $this->getCacheDirectory($url);

        $this->files->makeDirectory($cacheDirectory, 0755, true);

        $cacheDirectory = Str::finish($cacheDirectory, '/');

        $contents = str_replace('{', '__NOCACHE_LEFT_BRACE', $contents);
        $contents = str_replace('}', '__NOCACHE_RIGHT_BRACE', $contents);

        $sectionNames = array_keys($sections);

        while (Str::contains($contents, $sectionNames)) {
            foreach ($sections as $sectionName => $sectionContents) {
                $contents = str_replace($sectionName, $this->makeEvaluateTag($sectionName), $contents);
            }
        }

        $contextFile = $cacheDirectory.'.context';
        $this->files->put($contextFile, serialize($contexts));

        $manifestFile = $cacheDirectory.'.nocache.manifest';
        $templateFile = $cacheDirectory.'.template.antlers.html';

        $this->files->put($manifestFile, json_encode($sections));
        $this->files->put($templateFile, $contents);
    }

    /**
     * Returns the cacheable URL from the request.
     *
     * @param  Request  $request  The request.
     * @return mixed|string
     */
    public function getUrl(Request $request)
    {
        $url = $request->getUri();

        return explode('?', $url)[0];
    }

    /**
     * Restores a previous no_cache session from disk.
     *
     * @param  Request  $request
     * @return array|string|string[]
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function restoreSession(Request $request)
    {
        $url = $this->getUrl($request);
        $cacheDirectory = $this->cacheDirectory.$this->makeKey($url);
        $cacheDirectory = Str::finish($cacheDirectory, '/');
        $manifestFile = $cacheDirectory.'.nocache.manifest';
        $manifest = json_decode($this->files->get($manifestFile), true);

        $sectionNames = array_keys($manifest);
        $cascade = $this->cascade->instance()->hydrate()->toArray();
        $contexts = [];

        $contextCacheFile = $cacheDirectory.'.context';

        if ($this->files->exists($contextCacheFile)) {
            $contexts = unserialize($this->files->get($contextCacheFile));
        }

        if (! is_array($contexts)) {
            $contexts = [];
        }

        foreach ($manifest as $regionName => $contents) {
            if (! array_key_exists($regionName, $contexts)) {
                $contexts[$regionName] = $cascade;
            } else {
                $contexts[$regionName] = array_merge($cascade, $contexts[$regionName]);
            }
        }

        $this->session()->setSections($manifest)->setContexts($contexts);
        $templateFile = $cacheDirectory.'.template.antlers.html';

        $template = $this->files->get($templateFile);

        $result = (string) $this->parser->parse($template);

        if (Str::contains($result, $sectionNames)) {
            while (Str::contains($result, $sectionNames)) {
                foreach ($manifest as $regionName => $regionContent) {
                    if (Str::contains($regionContent, '{{')) {
                        $contextData = [];

                        if (array_key_exists($regionName, $contexts)) {
                            $contextData = $contexts[$regionName];
                        }

                        $replaceResult = (string) $this->parser->parse($regionContent, $contextData);
                        $result = str_replace($regionName, $replaceResult, $result);
                    } else {
                        $result = str_replace($regionName, $regionContent, $result);
                    }
                }
            }
        }

        $result = str_replace('__NOCACHE_LEFT_BRACE', '{', $result);

        return str_replace('__NOCACHE_RIGHT_BRACE', '}', $result);
    }

    /**
     * Get a config value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return $this->config->get($key, $default);
    }
}