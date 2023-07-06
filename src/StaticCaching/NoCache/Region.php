<?php

namespace Statamic\StaticCaching\NoCache;

use Statamic\Support\Arr;

abstract class Region
{
    protected $key;
    protected $context = [];
    protected $session;

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function placeholder(): string
    {
        return sprintf('<span class="nocache" data-nocache="%s">NOCACHE_PLACEHOLDER</span>', $this->key());
    }

    public function context(): array
    {
        return $this->context;
    }

    protected function filterContext(array $context)
    {
        foreach (['__env', 'app', 'errors', 'resolve', 'resolveComponentsUsing', 'forgetComponentsResolver', 'forgetFactory', 'flushCache', 'constructor'] as $var) {
            unset($context[$var]);
        }

        return $this->arrayRecursiveDiff($context, $this->session->cascade());
    }

    public function fragmentData(): array
    {
        return array_merge($this->session->cascade(), $this->context());
    }

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
                        $data[$aKey] = $aValue;
                    }
                }
            } else {
                $data[$aKey] = $aValue;
            }
        }

        return $data;
    }

    public function __serialize(): array
    {
        return Arr::except(get_object_vars($this), ['session']);
    }

    public function __unserialize(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        $this->session = app(Session::class);
    }
}
