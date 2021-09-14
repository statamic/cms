<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\Support\Str;
use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class StringLibrary extends RuntimeLibrary
{
    protected $name = ['str', 'string'];

    protected $exposedMethods = [];

    public function __construct()
    {
        $this->exposedMethods = [
            'startsWith' => [
                $this->stringVar('haystack'),
                $this->strOrArrayVar('needle'),
            ],
            'sentenceList' => [
                $this->arrayVar('list'),
                $this->stringVarWithDefault('glue', 'and'),
                $this->boolVarWithDefault('oxford_comma', true),
            ],
            'slug' => [
                $this->stringVar('string'),
                $this->stringVarWithDefault('separator', '-'),
                $this->stringVarWithDefault('language', 'en'),
            ],
            'studlyToSlug' => [$this->stringVar('string')],
            'studlyToTitle' => [$this->stringVar('string')],
            'studlyToWords' => [$this->stringVar('string')],
            'slugToTitle' => [$this->stringVar('string')],
            'isUrl' => [$this->stringVar('string')],
            'deslugify' => [$this->stringVar('string')],
            'fileSizeForHumans' => [
                $this->numericVar('bytes'),
                $this->numericVarWithDefault('decimals', 2),
            ],
            'timeForHumans' => [
                $this->numericVar('ms'),
            ],
            'widont' => [
                $this->stringVar('value'),
                $this->numericVarWithDefault('words', 1),
            ],
            'compare' => [
                $this->stringVar('string1'),
                $this->stringVar('string2'),
            ],
            'bool' => [$this->boolVar('bool')],
            'replaceArray' => [
                $this->stringVar('search'),
                $this->arrayVar('replace'),
                $this->stringVar('subject'),
            ],
            'afterLast' => [
                $this->stringVar('subject'),
                $this->stringVar('search'),
            ],
            'ascii' => [
                $this->stringVar('value'),
                $this->stringVarWithDefault('language', 'en'),
            ],
            'before' => [
                $this->stringVar('subject'),
                $this->stringVar('search'),
            ],
            'beforeLast' => [
                $this->stringVar('subject'),
                $this->stringVar('search'),
            ],
            'between' => [
                $this->stringVar('subject'),
                $this->stringVar('from'),
                $this->stringVar('to'),
            ],
            'camel' => [$this->stringVar('value')],
            'containsAll' => [
                $this->stringVar('haystack'),
                $this->arrayVar('needles'),
            ],
            'contains' => [
                $this->stringVar('haystack'),
                $this->strOrArrayVar('needles'),
            ],
            'endsWith' => [
                $this->stringVar('haystack'),
                $this->strOrArrayVar('needles'),
            ],
            'finish' => [
                $this->stringVar('value'),
                $this->stringVar('cap'),
            ],
            'is' => [
                $this->strOrArrayVar('pattern'),
                $this->stringVar('value'),
            ],
            'isUuid' => [$this->stringVar('value')],
            'kebab' => [$this->stringVar('kebab')],
            'length' => [
                $this->stringVar('value'),
                $this->stringVarWithDefault('encoding', null),
            ],
            'limit' => [
                $this->stringVar('limit'),
                $this->numericVarWithDefault('limit', 100),
                $this->stringVarWithDefault('end', '...'),
            ],
            'lower' => [$this->stringVar('lower')],
            'words' => [
                $this->stringVar('limit'),
                $this->numericVarWithDefault('limit', 100),
                $this->stringVarWithDefault('end', '...'),
            ],
            'match' => [
                $this->stringVar('pattern'),
                $this->stringVar('subject'),
            ],
            'matchAll' => [
                $this->stringVar('pattern'),
                $this->stringVar('subject'),
            ],
            'padBoth' => [
                $this->stringVar('value'),
                $this->numericVar('length'),
                $this->stringVarWithDefault('pad', ' '),
            ],
            'padLeft' => [
                $this->stringVar('value'),
                $this->numericVar('length'),
                $this->stringVarWithDefault('pad', ' '),
            ],
            'padRight' => [
                $this->stringVar('value'),
                $this->numericVar('length'),
                $this->stringVarWithDefault('pad', ' '),
            ],
            'plural' => [
                $this->stringVar('value'),
                $this->numericVarWithDefault('count', 2),
            ],
            'pluralStudly' => [
                $this->stringVar('value'),
                $this->numericVarWithDefault('count', 2),
            ],
            'random' => [$this->numericVarWithDefault('length', 16)],
            'repeat' => [
                $this->stringVar('string'),
                $this->numericVar('times'),
            ],
            'replace' => [
                $this->strOrArrayVar('search'),
                $this->strOrArrayVar('replace'),
                $this->strOrArrayVar('subject'),
            ],
            'replaceFirst' => [
                $this->stringVar('search'),
                $this->stringVar('replace'),
                $this->stringVar('subject'),
            ],
            'replaceLast' => [
                $this->stringVar('search'),
                $this->stringVar('replace'),
                $this->stringVar('subject'),
            ],
            'remove' => [
                $this->strOrArrayVar('search'),
                $this->stringVar('subject'),
                $this->boolVarWithDefault('case_sensitive', true),
            ],
            'start' => [
                $this->stringVar('value'),
                $this->stringVar('prefix'),
            ],
            'upper' => [$this->stringVar('value')],
            'title' => [$this->stringVar('value')],
            'singular' => [$this->stringVar('value')],
            'snake' => [$this->stringVar('value'), $this->stringVarWithDefault('delimiter', '_')],
            'studly' => [$this->stringVar('value')],
            'substr' => [
                $this->stringVar('string'),
                $this->numericVar('start'),
                $this->numericVarWithDefault('length', null),
            ],
            'ucfirst' => [$this->stringVar('string')],
            'uuid' => 1,
            'wordCount' => [$this->stringVar('string')],
            'substrCount' => [
                $this->stringVar('haystack'),
                $this->stringVar('needle'),
                $this->numericVarWithDefault('offset', 0),
                $this->numericVarWithDefault('length', null),
            ],
            'reverse' => [$this->stringVar('value')],
            'trim' => [$this->stringVar('value')],
        ];
    }

    public function trim($value)
    {
        return trim($value);
    }

    public function substrCount($haystack, $needle, $offset = 0, $length = null)
    {
        // From: https://github.com/laravel/framework/blob/8.x/src/Illuminate/Support/Str.php#L801-L808
        if (! is_null($length)) {
            return substr_count($haystack, $needle, $offset, $length);
        } else {
            return substr_count($haystack, $needle, $offset);
        }
    }

    public function wordCount($string)
    {
        return \str_word_count($string);
    }

    public function uuid()
    {
        return (string) Str::uuid();
    }

    public function ucfirst($string)
    {
        return Str::ucfirst($string);
    }

    public function substr($string, $start, $length = null)
    {
        return Str::substr($string, $start, $length);
    }

    public function studly($value)
    {
        return Str::studly($value);
    }

    public function snake($value, $delimiter = '_')
    {
        return Str::snake($value, $delimiter);
    }

    public function singular($value)
    {
        return Str::singular($value);
    }

    public function reverse($value)
    {
        return strrev($value);
    }

    public function title($value)
    {
        return Str::title($value);
    }

    public function upper($value)
    {
        return Str::upper($value);
    }

    public function start($value, $prefix)
    {
        return Str::start($value, $prefix);
    }

    public function remove($search, $subject, $case_sensitive = true)
    {
        // From: https://github.com/laravel/framework/blob/8.x/src/Illuminate/Support/Str.php#L633-L640
        return $case_sensitive
            ? str_replace($search, '', $subject)
            : str_ireplace($search, '', $subject);
    }

    public function replaceLast($search, $replace, $subject)
    {
        return Str::replaceLast($search, $replace, $subject);
    }

    public function replaceFirst($search, $replace, $subject)
    {
        return Str::replaceFirst($search, $replace, $subject);
    }

    public function replace($search, $replace, $subject)
    {
        return Str::replace($search, $replace, $subject);
    }

    public function replaceArray($search, $replace, $subject)
    {
        return Str::replaceArray($search, $replace, $subject);
    }

    public function repeat($string, $times)
    {
        return Str::repeat($string, $times);
    }

    public function random($length = 16)
    {
        return Str::random($length);
    }

    public function pluralStudly($value, $count = 2)
    {
        return Str::pluralStudly($value, $count);
    }

    public function plural($value, $count = 2)
    {
        return Str::plural($value, $count);
    }

    public function padLeft($value, $length, $pad = ' ')
    {
        return Str::padLeft($value, $length, $pad);
    }

    public function padRight($value, $length, $pad = ' ')
    {
        return Str::padRight($value, $length, $pad);
    }

    public function padBoth($value, $length, $pad = ' ')
    {
        return Str::padBoth($value, $length, $pad);
    }

    public function matchAll($pattern, $subject)
    {
        // From: https://github.com/laravel/framework/blob/8.x/src/Illuminate/Support/Str.php#L422-L431
        preg_match_all($pattern, $subject, $matches);

        if (empty($matches[0])) {
            return [];
        }

        return $matches[1] ?? $matches[0];
    }

    public function match($pattern, $subject)
    {
        // From: https://github.com/laravel/framework/blob/8.x/src/Illuminate/Support/Str.php#L404-L413
        preg_match($pattern, $subject, $matches);

        if (! $matches) {
            return '';
        }

        return $matches[1] ?? $matches[0];
    }

    public function words($value, $limit = 100, $end = '...')
    {
        return Str::words($value, $limit, $end);
    }

    public function lower($value)
    {
        return Str::lower($value);
    }

    public function limit($value, $limit = 100, $end = '...')
    {
        return Str::limit($value, $limit, $end);
    }

    public function length($value, $encoding = null)
    {
        return Str::length($value, $encoding);
    }

    public function kebab($value)
    {
        return Str::kebab($value);
    }

    public function isUuid($value)
    {
        return Str::isUuid($value);
    }

    public function is($pattern, $value)
    {
        return Str::is($pattern, $value);
    }

    public function finish($value, $cap)
    {
        return Str::finish($value, $cap);
    }

    public function endsWith($haystack, $needles)
    {
        return Str::endsWith($haystack, $needles);
    }

    public function contains($haystack, $needles)
    {
        return Str::contains($haystack, $needles);
    }

    public function containsAll($haystack, $needles)
    {
        return Str::containsAll($haystack, $needles);
    }

    public function camel($value)
    {
        return Str::camel($value);
    }

    public function between($subject, $from, $to)
    {
        return Str::between($subject, $from, $to);
    }

    public function beforeLast($subject, $search)
    {
        return Str::beforeLast($subject, $search);
    }

    public function before($subject, $search)
    {
        return Str::before($subject, $search);
    }

    public function ascii($value, $language = 'en')
    {
        return Str::ascii($value, $language);
    }

    public function afterLast($subject, $search)
    {
        return Str::afterLast($subject, $search);
    }

    public function startsWith($haystack, $needle)
    {
        return Str::startsWith($haystack, $needle);
    }

    public function sentenceList($list, $glue = 'and', $oxford_comma = true)
    {
        return Str::makeSentenceList($list, $glue, $oxford_comma);
    }

    public function slug($string, $separator = '-', $language = 'en')
    {
        return Str::slug($string, $separator, $language);
    }

    public function studlyToSlug($string)
    {
        return Str::studlyToSlug($string);
    }

    public function studlyToTitle($string)
    {
        return Str::studlyToTitle($string);
    }

    public function studlyToWords($string)
    {
        return Str::studlyToWords($string);
    }

    public function slugToTitle($string)
    {
        return Str::slugToTitle($string);
    }

    public function isUrl($string)
    {
        return Str::isUrl($string);
    }

    public function deslugify($string)
    {
        return Str::deslugify($string);
    }

    public function fileSizeForHumans($bytes, $decimals = 2)
    {
        return Str::fileSizeForHumans($bytes, $decimals);
    }

    public function timeForHumans($ms)
    {
        return Str::timeForHumans($ms);
    }

    public function widont($value, $words = 1)
    {
        return Str::widont($value, $words);
    }

    public function compare($str1, $str2)
    {
        return Str::compare($str1, $str2);
    }

    public function bool($bool)
    {
        return Str::bool($bool);
    }
}
