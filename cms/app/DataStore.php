<?php

namespace Statamic;

use Statamic\API\Arr;
use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\API\Parse;

/**
 * The DataStore holds all variables, organized into scopes.
 */
class DataStore
{
    /**
     * The name of the default scope
     * @var string
     */
    const DEFAULT_SCOPE = 'cascade';

    /**
     * The DataStore
     * @var array
     */
    private $data = [];

    /**
     * Variables leveraging environments
     * @var array
     */
    private $env = [];

    /**
     * "Create" the DataStore using these variables. This overrides everything.
     *
     * @param array $variables
     */
    public function create($variables)
    {
        $this->data = $variables;
    }

    /**
     * Create a scope
     * A scope is essentially a key in the array that holds keys in
     * order to prevent everything being dumped on the global scope.
     *
     * @param string $scope      Name of scope
     * @param array  $variables
     */
    public function createScope($scope, $variables = [])
    {
        if (! isset($this->data[$scope])) {
            $this->data[$scope] = $this->parseEnv($variables, $scope);
        }
    }

    /**
     * Check if a scope exists
     *
     * @param string $scope  Name of scope
     * @return bool
     */
    public function isScope($scope)
    {
        return (bool) array_get($this->data, $scope);
    }

    /**
     * Merge variables into an existing scope
     *
     * @param array  $variables  Variables to merge
     * @param string $scope      Scope to merge into
     */
    public function merge($variables, $scope = null)
    {
        $scope = $scope ?: self::DEFAULT_SCOPE;

        $variables = $this->parseEnv($variables, $scope);

        $existing = array_get($this->data, $scope, []);

        array_set($this->data, $scope, Arr::combineRecursive($existing, $variables));
    }

    /**
     * Merge variables into an existing scope
     * Same as `merge`, but $scope as first argument for readability
     *
     * @param string $scope      Scope to merge into
     * @param array  $variables  Variables to merge
     */
    public function mergeInto($scope, $variables)
    {
        $this->merge($variables, $scope);
    }

    /**
     * Get a scope's variables
     *
     * @param string $scope   Scope name
     * @param array $default  Data to return if scope doesn't exist
     * @return array
     */
    public function getScope($scope, $default = [])
    {
        if (! $this->isScope($scope)) {
            return $default;
        }

        return array_get($this->data, $scope);
    }

    public function getEnvInScope($scope, $default = [])
    {
        return array_get($this->env, $scope, $default);
    }

    public function mergeIntoEnv($variables, $scope = null)
    {
        array_set($this->env, $scope, $variables);
    }

    public function removeScope($scope)
    {
        unset($this->data[$scope]);
    }

    /**
     * Get all variables in the DataStore
     *
     * Return an array of all data, with the default scope's data
     * in the first level of the array.
     *
     * @return array
     */
    public function getAll()
    {
        $output = array();

        foreach ($this->data as $scope => $variables) {
            if ($scope === self::DEFAULT_SCOPE) {
                $output = $variables + $output;
            } else {
                $output[$scope] = $variables;
            }
        }

        return $output;
    }

    /**
     * Parse environment variable placeholders with the actual values
     *
     * @param   mixed  $value  The value to parse
     * @param   mixed  $scope  Where the value is being added
     * @return  mixed
     */
    private function parseEnv($value, $scope)
    {
        // Keep track of whether we started with an array
        $is_array = (is_array($value));

        // Make it into an array so we can keep things consistent
        $array = Helper::ensureArray($value);

        foreach ($array as $key => $val) {
            if (is_array($val)) {
                // An array? Recursion please.
                $array[$key] = $this->parseEnv($val, $scope.'.'.$key);
            } else {
                if (is_string($val) && Str::contains($val, '{env:')) {
                    $parsed = Parse::env($val);

                    // Keep track of it
                    array_set($this->env, $scope.'.'.$key, [
                        'raw' => $val,
                        'parsed' => $parsed
                    ]);
                } else {
                    // No environment variable
                    $parsed = $val;
                }

                $array[$key] = $parsed;
            }
        }

        // Return the array if we want one, or just a value.
        return ($is_array) ? $array : reset($array);
    }
}
