<?php

namespace Statamic\View\Antlers\Language\Runtime\Debugging;

use Carbon\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Statamic\Auth\File\User;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Value;
use Statamic\Sites\Site;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Structures\Page;
use Statamic\Taxonomies\Taxonomy;
use Statamic\View\Antlers\Language\Runtime\Sandbox\RuntimeValues;

class ScopeDumper
{
    const HANDLE_ROOT = 'root';
    const HANDLE_CONTEXTUAL = 'contextual';
    const HANDLE_OTHER = 'other';
    const HANDLE_ADDITIONAL = 'additional';

    protected static $contextualKeys = [
        'environment' => 1, 'xml_header' => 1, 'csrf_token' => 1, 'csrf_field' => 1,
        'config' => 1, 'response_code' => 1, 'logged_in' => 1, 'logged_out' => 1,
        'current_date' => 1, 'now' => 1, 'today' => 1, 'current_url' => 1,
        'current_full_url' => 1, 'current_uri' => 1, 'get_post' => 1, 'get' => 'get',
        'post' => 1, 'old' => 1, 'site' => 1, 'sites' => 1, 'homepage' => 1, 'cp_url' => 1,
    ];

    protected static $moveToContextual = [
        'csrf_token' => 1, 'xml_header' => 1,
    ];

    private static $varIndexCount = 0;
    private $additionalVars = [];

    public function dump($data)
    {
        $contextual = [];
        $root = [];
        $other = [];

        foreach ($data as $k => $v) {
            if (array_key_exists($k, self::$contextualKeys)) {
                $contextual[$k] = $v;

                continue;
            }

            if (is_string($v) || is_numeric($v) || is_bool($v) || is_null($v)) {
                $root[$k] = $v;

                continue;
            }

            if ($k == 'app') {
                continue;
            }

            if (Str::startsWith($k, '__')) {
                continue;
            }

            // Move dates to root.
            if ($v instanceof Carbon) {
                $root[$k] = $this->dumpCarbon($v);

                continue;
            }

            $other[$k] = $v;
        }

        $root = $this->createVariables($root);
        $contextual = $this->createVariables($contextual);
        $other = $this->createVariables($other);

        /**
         * @var string $var
         * @var DumpVariable $val
         */
        foreach ($contextual as $var => $val) {
            if ($val->variablesReference === 0) {
                $root[$var] = $val;
                unset($contextual[$var]);
            }
        }

        /**
         * @var string $var
         * @var DumpVariable $val
         */
        foreach ($other as $var => $val) {
            if ($val->variablesReference === 0) {
                $root[$var] = $val;
                unset($other[$var]);
            }
        }

        /**
         * @var string $var
         * @var DumpVariable $val
         */
        foreach ($root as $var => $val) {
            if (array_key_exists($val->name, self::$moveToContextual)) {
                $contextual[] = $val;
                unset($root[$var]);
            }
        }

        return [
            self::HANDLE_CONTEXTUAL => $contextual,
            self::HANDLE_OTHER => $other,
            self::HANDLE_ROOT => $root,
            self::HANDLE_ADDITIONAL => $this->additionalVars,
        ];
    }

    private function dumpCarbon(Carbon $carbon)
    {
        return $carbon->toAtomString().' {Carbon}';
    }

    private function createVariables($data)
    {
        $vars = [];

        foreach ($data as $k => $v) {
            $variable = new DumpVariable();
            $variable->name = strval($k);

            if (is_string($v)) {
                $variable->value = $v;
                $vars[] = $variable;

                continue;
            } elseif (is_bool($v)) {
                if ($v) {
                    $variable->value = 'true';
                } else {
                    $variable->value = 'false';
                }

                $vars[] = $variable;

                continue;
            } elseif (is_null($v)) {
                $variable->value = 'null';
                $vars[] = $variable;

                continue;
            } elseif (is_numeric($v)) {
                $variable->value = strval($v);
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof HtmlString) {
                $variable->value = $v->toHtml();
                $vars[] = $variable;

                continue;
            } elseif (is_array($v)) {
                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $this->additionalVars[$thisVar] = $this->createVariables($v);

                $variable->value = '{array['.count($v).']}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof Carbon) {
                $variable->value = $this->dumpCarbon($v);
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof Site) {
                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $this->additionalVars[$thisVar] = $this->createVariables(RuntimeValues::resolveWithRuntimeIsolation($v));

                $variable->value = '{site}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof \Illuminate\Support\Collection) {
                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $this->additionalVars[$thisVar] = $this->createVariables($v->all());

                $variable->value = '{Collection['.$v->count().']}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof ViewErrorBag) {
                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $this->additionalVars[$thisVar] = $this->createVariables($v->all());

                $variable->value = '{ViewErrorBag['.$v->count().']}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof Value) {
                $resolvedValue = null;

                if ($v instanceof Augmentable) {
                    $resolvedValue = RuntimeValues::resolveWithRuntimeIsolation($v);
                } else {
                    $resolvedValue = $v->value();
                }

                if (is_object($resolvedValue) || is_array($resolvedValue)) {
                    if ($resolvedValue instanceof Carbon) {
                        $variable->value = $this->dumpCarbon($resolvedValue);
                        $vars[] = $variable;

                        continue;
                    }

                    if (is_array($resolvedValue)) {
                        self::$varIndexCount += 1;
                        $thisVar = self::$varIndexCount;
                        $this->additionalVars[$thisVar] = $this->createVariables($resolvedValue);

                        $variable->value = '{array['.count($resolvedValue).']}';
                        $variable->variablesReference = $thisVar;

                        continue;
                    } else {
                        $resolvedValue = $v->value();
                    }
                }

                if (is_numeric($resolvedValue)) {
                    $variable->value = strval($resolvedValue);
                } elseif (is_null($resolvedValue)) {
                    $variable->value = 'null';
                } elseif (is_bool($resolvedValue)) {
                    $variable->value = $resolvedValue ? 'true' : 'false';
                } else {
                    $variable->value = $resolvedValue;
                }

                $vars[] = $variable;

                continue;
            } elseif ($v instanceof Collection) {
                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $subVar = $this->createVariables(RuntimeValues::resolveWithRuntimeIsolation($v));
                $this->additionalVars[$thisVar] = $subVar;

                $variable->value = '{Entries\Collection['.count($subVar).']}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof Page) {
                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $subVar = $this->createVariables(RuntimeValues::resolveWithRuntimeIsolation($v));
                $this->additionalVars[$thisVar] = $subVar;

                $variable->value = '{Page['.count($subVar).']}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof EntryQueryBuilder) {
                $subVar = $v->get()->all();

                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $subVar = $this->createVariables($subVar);
                $this->additionalVars[$thisVar] = $subVar;

                $variable->value = '{EntryQuery['.count($subVar).']}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof Entry) {
                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $subVar = $this->createVariables(RuntimeValues::resolveWithRuntimeIsolation($v));
                $this->additionalVars[$thisVar] = $subVar;

                $variable->value = '{Entry['.count($subVar).']}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof Blueprint) {
                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $subVar = $this->createVariables(RuntimeValues::resolveWithRuntimeIsolation($v));
                $this->additionalVars[$thisVar] = $subVar;

                $variable->value = '{Blueprint['.count($subVar).']}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof User) {
                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $subVar = $this->createVariables(RuntimeValues::resolveWithRuntimeIsolation($v));
                $this->additionalVars[$thisVar] = $subVar;

                $variable->value = '{User['.count($subVar).']}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof \Statamic\Auth\Eloquent\User) {
                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $subVar = $this->createVariables(RuntimeValues::resolveWithRuntimeIsolation($v));
                $this->additionalVars[$thisVar] = $subVar;

                $variable->value = '{EloquentUser['.count($subVar).']}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            } elseif ($v instanceof Taxonomy) {
                self::$varIndexCount += 1;
                $thisVar = self::$varIndexCount;
                $subVar = $this->createVariables(RuntimeValues::resolveWithRuntimeIsolation($v));
                $this->additionalVars[$thisVar] = $subVar;

                $variable->value = '{Taxonomy['.count($subVar).']}';
                $variable->variablesReference = $thisVar;
                $vars[] = $variable;

                continue;
            }
        }

        return $vars;
    }
}
