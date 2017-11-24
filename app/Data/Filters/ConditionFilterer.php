<?php

namespace Statamic\Data\Filters;

use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\Contracts\Data\Filters\ConditionFilterer as ConditionFiltererContract;

class ConditionFilterer implements ConditionFiltererContract
{
    /**
     * @param \Statamic\Data\DataCollection $collection The collection to be filtered
     * @param array $conditions                         The conditions to be matched
     * @return \Statamic\Data\DataCollection
     */
    public function filter($collection, $conditions)
    {
        foreach ($conditions as $parameter => $needles) {
            $needles = Helper::ensureArray($needles);

            $collection = $collection->filter(function($item) use ($needles, $parameter) {
                foreach ($needles as $needle) {
                    list($haystack, $type) = explode(':', $parameter);

                    $value = $this->getHaystackValue($item, $haystack);

                    if (! $this->getExpression($type, $needle, $value)) {
                        return false;
                    }
                }

                return true;
            });
        }

        return $collection;
    }

    /**
     * Get the real value from the item
     *
     * @param \Statamic\Contracts\Data\Data $data The data item to use
     * @param string                        $value                 The value to find
     * @return mixed
     */
    private function getHaystackValue($data, $value)
    {
        switch ($value) {
            case 'url':
                $value = $data->url();
                break;
            case 'slug':
                $value = $data->slug();
                break;
            case 'collection':
                if ($data instanceof \Statamic\Contracts\Data\Entries\Entry) {
                    $value = $data->collectionName();
                }
                break;
            default:
                $value = $data->getWithCascade($value);
                break;
        }

        return $value;
    }

    /**
     * Get the outcome of a condition expression
     *
     * @param string $type  The type of condition
     * @param mixed $needle The value we we've requested
     * @param mixed $value  The value from the actual collection item
     * @return bool|int
     */
    private function getExpression($type, $needle, $value)
    {
        // Keep a copy of the un-lowercased values. It's rarer that we want these.
        // We'll need them 'prepared' which would convert "true" to a boolean, etc.
        $original_value = $value = $this->prepareValue($value);
        $original_needle = $needle = $this->prepareValue($needle);

        // Lowercase the values. Most of the time we need them like this.
        $value = (is_string($value)) ? strtolower($value) : $value;
        $needle = (is_string($needle)) ? strtolower($needle) : $needle;

        switch ($type) {
            case 'is':
            case 'equals':
                return $value == $needle;
                break;
            case 'not':
            case 'isnt':
            case 'aint':
            case '¯\_(ツ)_/¯':
                return $value != $needle;
                break;
            case 'is_strict':
            case 'equals_strict':
                return $value === $needle;
                break;
            case 'not_strict':
            case 'isnt_strict':
            case 'aint_strict':
                return $value !== $needle;
                break;
            case 'exists':
            case 'isset':
                return $value !== null;
                break;
            case 'doesnt_exist':
            case 'not_set':
            case 'isnt_set':
            case 'null':
                return $value === null;
                break;
            case 'contains':
                return (is_array($value)) ? in_array($needle, $value) : Str::contains($value, $needle);
                break;
            case 'doesnt_contain':
                return (is_array($value)) ? ! in_array($needle, $value) : ! Str::contains($value, $needle);
                break;
            case 'starts_with':
            case 'begins_with':
                return Str::startsWith($value, $needle);
                break;
            case 'doesnt_start_with':
            case 'doesnt_begin_with':
                return ! Str::startsWith($value, $needle);
                break;
            case 'ends_with':
                return Str::endsWith($value, $needle);
                break;
            case 'doesnt_end_with':
                return ! Str::endsWith($value, $needle);
                break;
            case 'matches':
            case 'match':
            case 'regex':
                return (is_array($original_value)) ? preg_grep($original_needle, $original_value) : preg_match($original_needle, $original_value);
                break;
            default:
                try {
                    return modify($value)->$type($needle)->fetch();
                } catch(Exception $e) {
                    \Log::notice("[$type] is not a valid condition");
                }
        }
    }

    /**
     * Prepare a value for comparisons
     *
     * @param  mixed $value
     * @return mixed
     */
    private function prepareValue($value)
    {
        if (is_string($value)) {
            $string = strtolower($value);

            switch ($string) {
                case 'true':
                case 'yes':
                    return true;
                case 'false':
                case 'no':
                    return false;
                default:
                    return $value;
            }
        }

        return $value;
    }
}
