<?php

namespace Statamic\StaticCaching\NoCache;

use Closure;

class CacheSession
{
    /**
     * The session's entry/page id.
     *
     * When set, this will be used to maintain an index between
     * entries and the different nocache files for each URL.
     *
     * @var string|null
     */
    protected $id = null;

    /**
     * The session's view path.
     *
     * @var string
     */
    protected $viewPath = '';

    /**
     * The initial root (Cascade) data for this session.
     *
     * @var array
     */
    protected $rootData = [];

    /**
     * A list of all nocache region contents.
     *
     * @var array
     */
    protected $noCacheSections = [];

    /**
     * A list of data contexts for each nocache region.
     *
     * @var array
     */
    protected $noCacheContexts = [];

    /**
     * Maintains a list of evaluated content for the initial response.
     *
     * @var array
     */
    protected $immediateResults = [];

    /**
     * Keeps track of how nested the current session is.
     *
     * @var int
     */
    protected $stack = 0;

    /**
     * Indicates if the current session is locked.
     *
     * Locked sessions will not have their view paths/identifiers updated.
     *
     * @var bool
     */
    protected $isLocked = false;

    /**
     * A list of variable names that will be removed before caching.
     *
     * @var string[]
     */
    protected $ignoreVars = [
        '__env', 'app', 'errors',
    ];

    /**
     * Sets the session's root data.
     *
     * @param  array  $data  The root data.
     * @return $this
     */
    public function setRootData($data)
    {
        $this->rootData = $data;

        return $this;
    }

    /**
     * Seeds the session's sections.
     *
     * @param  array  $sections  The sections.
     * @return $this
     */
    public function setSections($sections)
    {
        $this->noCacheSections = $sections;

        return $this;
    }

    /**
     * Seeds the session's contexts.
     *
     * @param  array  $contexts  The data contexts.
     * @return $this
     */
    public function setContexts($contexts)
    {
        $this->noCacheContexts = $contexts;

        return $this;
    }

    /**
     * Tests if the session has the section.
     *
     * @param  string  $region  The region name.
     * @return bool
     */
    public function hasSection($region)
    {
        return array_key_exists($region, $this->noCacheSections);
    }

    /**
     * Returns the data for the requested nocache region.
     *
     * @param  string  $region  The region name.
     * @return array|mixed
     */
    public function getSectionData($region)
    {
        if (array_key_exists($region, $this->noCacheContexts)) {
            return $this->noCacheContexts[$region];
        }

        return [];
    }

    /**
     * Retrieves the content for the provided section.
     *
     * @param  string  $region  The region name.
     * @return mixed
     */
    public function getSectionContent($region)
    {
        return $this->noCacheSections[$region];
    }

    /**
     * Sets the session's entry identifier.
     *
     * @param  object|string  $id  The identifier.
     * @return $this
     */
    public function setId($id)
    {
        if ($this->isLocked) {
            return $this;
        }

        if (is_object($id) && method_exists($id, '__toString')) {
            $id = (string) $id;
        }

        $this->id = $id;

        return $this;
    }

    /**
     * Sets the session's view path.
     *
     * @param  string  $viewPath  The view path to set.
     * @return $this
     */
    public function setViewPath($viewPath)
    {
        if ($this->isLocked) {
            return $this;
        }

        $this->stack += 1;
        $this->viewPath = $viewPath;
        $this->isLocked = true;

        return $this;
    }

    /**
     * Compares two arrays and returns the difference, recursively.
     *
     * This function will also remove Closures.
     *
     * @param  array  $a  The starting array.
     * @param  array  $b  The array to compare it to.
     * @return array
     */
    private function arrayRecursiveDiff($a, $b)
    {
        $data = [];

        foreach ($a as $aKey => $aValue) {
            if (! is_object($aKey) && is_array($b) && array_key_exists($aKey, $b)) {
                if (is_array($aValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($aValue, $b[$aKey]);

                    if (! empty($aRecursiveDiff)) {
                        $data[$aKey] = $aRecursiveDiff;
                    }
                } else {
                    if ($aValue != $b[$aKey]) {
                        if (($aValue instanceof Closure) == false) {
                            $data[$aKey] = $aValue;
                        }
                    }
                }
            } else {
                if (($aValue instanceof Closure) == false) {
                    $data[$aKey] = $aValue;
                }
            }
        }

        return $data;
    }

    /**
     * Adds a new section to the nocache session.
     *
     * @param  string  $contents  The original Antlers template code.
     * @param  array  $context  The context data required to re-evaluate the Antlers code.
     * @param  string  $result  The pre-rendered result to render the initial response.
     * @return string
     */
    public function pushSection($contents, $context, $result)
    {
        foreach ($this->ignoreVars as $varName) {
            unset($context[$varName]);
        }
        $context = $this->arrayRecursiveDiff($context, $this->rootData);

        $this->stack += 1;
        $contents = trim($contents);
        $contentKey = '__no_cache_section_'.sha1($contents.$this->stack);

        $this->noCacheSections[$contentKey] = $contents;
        $this->noCacheContexts[$contentKey] = $context;

        // Store the already computed results for later. These are used to provide the initial response.
        $this->immediateResults[$contentKey] = $result;

        return $contentKey;
    }

    /**
     * Gets the session's entry id.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the session's nocache sections.
     *
     * @return array
     */
    public function getSections()
    {
        return $this->noCacheSections;
    }

    /**
     * Gets the session's nocache data contexts.
     *
     * @return array
     */
    public function getContexts()
    {
        return $this->noCacheContexts;
    }

    /**
     * Checks if the provided path is the active session.
     *
     * @param  string  $path  The view path.
     * @return bool
     */
    public function isActive($path)
    {
        if ($path != $this->viewPath) {
            return false;
        }

        return ! empty($this->immediateResults);
    }

    /**
     * Replaces all nocache regions with their evaluated results.
     *
     * @param  string  $contents  The contents.
     * @return string
     */
    public function prepareContents($contents)
    {
        foreach ($this->immediateResults as $regionName => $evaluatedResults) {
            $contents = str_replace($regionName, $evaluatedResults, $contents);
        }

        return $contents;
    }
}
