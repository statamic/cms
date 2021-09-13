<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox\TypeBoxing;

use Illuminate\Support\Traits\Macroable;
use Statamic\Support\Str;

class StringBoxObject
{
    use Macroable, AntlersBoxedStandardMethods;

    protected $value = '';

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function indexOf($search_string, $offset = 0)
    {
        return mb_strpos($this->value, $search_string, $offset);
    }

    public function lastIndexOf($search_string, $offset = 0)
    {
        return mb_strrpos($this->value, $search_string, $offset);
    }

    public function concat(...$values)
    {
        foreach ($values as $value) {
            $this->value .= $value;
        }

        return $this->value;
    }

    public function charAt($position)
    {
        return mb_substr($this->value, $position, 1);
    }

    public function charCodeAt($position)
    {
        return mb_ord($this->charAt($position));
    }

    public function stripTags($tags_list = [])
    {
        return Str::stripTags($this->value, $tags_list);
    }

    public function startsWith($needles)
    {
        return Str::startsWith($this->value, $needles);
    }

    public function upper()
    {
        return Str::upper($this->value);
    }

    public function lower()
    {
        return Str::lower($this->value);
    }

    public function toUpperCase()
    {
        return Str::upper($this->value);
    }

    public function toLowerCase()
    {
        return Str::lower($this->value);
    }

    public function trim()
    {
        return trim($this->value);
    }

    public function substrCount($needle, $offset = 0, $length = null)
    {
        return Str::substrCount($this->value, $needle, $offset, $length);
    }

    public function wordCount()
    {
        return Str::wordCount($this->value);
    }

    public function ucfirst()
    {
        return Str::ucfirst($this->value);
    }

    public function substr($start, $length = null)
    {
        return Str::substr($this->value, $start, $length);
    }

    public function studly()
    {
        return Str::studlyToWords($this->value);
    }

    public function snake($delimiter = '_')
    {
        return Str::snake($this->value, $delimiter);
    }

    public function singular()
    {
        return Str::singular($this->value);
    }

    public function reverse()
    {
        return strrev($this->value);
    }

    public function title()
    {
        return Str::title($this->value);
    }

    public function start($prefix)
    {
        return Str::start($this->value, $prefix);
    }

    public function remove($search, $case_sensitive = true)
    {
        return Str::remove($search, $this->value, $case_sensitive);
    }

    public function replaceLast($search, $replace, $subject)
    {
        return Str::replaceLast($search, $replace, $this->value);
    }

    public function replaceFirst($search, $replace)
    {
        return Str::replaceFirst($search, $replace, $this->value);
    }

    public function replace($search, $replace)
    {
        return Str::replace($search, $replace, $this->value);
    }

    public function replaceArray($search, $replace)
    {
        return Str::replaceArray($search, $replace, $this->value);
    }

    public function repeat($times)
    {
        return Str::repeat($this->value, $times);
    }

    public function pluralStudly($count = 2)
    {
        return Str::pluralStudly($this->value, $count);
    }

    public function plural($count = 2)
    {
        return Str::plural($this->value, $count);
    }

    public function padLeft($length, $pad = ' ')
    {
        return Str::padLeft($this->value, $length, $pad);
    }

    public function padRight($length, $pad = ' ')
    {
        return Str::padRight($this->value, $length, $pad);
    }

    public function padBoth($length, $pad = ' ')
    {
        return Str::padBoth($this->value, $length, $pad);
    }

    public function matchAll($pattern)
    {
        return Str::matchAll($pattern, $this->value)->all();
    }

    public function match($pattern)
    {
        return Str::matchAll($pattern, $this->value);
    }

    public function markdown($options = [])
    {
        return Str::markdown($this->value, $options);
    }

    public function words($limit = 100, $end = '...')
    {
        return Str::words($this->value, $limit, $end);
    }

    public function limit($limit = 100, $end = '...')
    {
        return Str::limit($this->value, $limit, $end);
    }

    public function length($encoding = null)
    {
        return Str::length($this->value, $encoding);
    }

    public function kebab()
    {
        return Str::kebab($this->value);
    }

    public function isUuid()
    {
        return Str::isUuid($this->value);
    }

    public function isAscii()
    {
        return Str::isAscii($this->value);
    }

    public function is($pattern)
    {
        return Str::is($pattern, $this->value);
    }

    public function finish($cap)
    {
        return Str::finish($this->value, $cap);
    }

    public function endsWith($needles)
    {
        return Str::endsWith($this->value, $needles);
    }

    public function contains($needles)
    {
        return Str::contains($this->value, $needles);
    }

    public function containsAll($needles)
    {
        return Str::containsAll($this->value, $needles);
    }

    public function camel()
    {
        return Str::camel($this->value);
    }

    public function between($from, $to)
    {
        return Str::between($this->value, $from, $to);
    }

    public function beforeLast($search)
    {
        return Str::beforeLast($this->value, $search);
    }

    public function before($search)
    {
        return Str::before($this->value, $search);
    }

    public function ascii($language = 'en')
    {
        return Str::ascii($this->value, $language);
    }

    public function afterLast($search)
    {
        return Str::afterLast($this->value, $search);
    }

    public function slug($separator = '-', $language = 'en')
    {
        return Str::slug($this->value, $separator, $language);
    }

    public function studlyToSlug()
    {
        return Str::studlyToSlug($this->value);
    }

    public function studlyToWords()
    {
        return Str::studlyToWords($this->value);
    }

    public function slugToTitle()
    {
        return Str::slugToTitle($this->value);
    }

    public function isUrl()
    {
        return Str::isUrl($this->value);
    }

    public function deslugify()
    {
        return Str::deslugify($this->value);
    }

    public function widont($words = 1)
    {
        return Str::widont($this->value, $words);
    }

    public function compare($str2)
    {
        return Str::compare($this->value, $str2);
    }

    public function split($delimiter, $limit = null)
    {
        return explode($delimiter, $this->value, $limit);
    }
}
