<?php

namespace Statamic;

use Carbon\Carbon;
use Statamic\API\URL;
use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\API\File;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Comparator\DateComparator;
use Symfony\Component\Finder\Comparator\NumberComparator;

class FileCollection extends Collection
{
    /**
     * Filter by size
     *
     * @param  string  $size  A size comparison, eg. "> 10K"
     * @return static
     */
    public function filterBySize($size)
    {
        $comparator = new NumberComparator($size);

        return $this->filter(function($path) use ($comparator) {
            return $comparator->test(File::size($path));
        });
    }

    /**
     * Filter by extension(s)
     *
     * @param   string|array  $extensions  Extension or array of extensions
     * @return  static
     */
    public function filterByExtension($extensions)
    {
        $extensions = Helper::ensureArray($extensions);

        return $this->filter(function($path) use ($extensions) {
            return in_array(File::extension($path), $extensions);
        });
    }

    /**
     * Reject by extension(s)
     *
     * @param   string|array  $extensions  Extension or array of extensions
     * @return  static
     */
    public function rejectByExtension($extensions)
    {
        $extensions = Helper::ensureArray($extensions);

        return $this->reject(function($path) use ($extensions) {
            return in_array(File::extension($path), $extensions);
        });
    }

    /**
     * Filter by a regular expression
     *
     * @param   string  $regex    The regular expression to match against
     * @return  static
     */
    public function filterByRegex($regex)
    {
        return $this->filter(function($path) use ($regex) {
            return preg_match($regex, $path);
        });
    }

    /**
     * Reject by a regular expression
     *
     * @param   string  $regex    The regular expression to match against
     * @return  static
     */
    public function rejectByRegex($regex)
    {
        return $this->reject(function($path) use ($regex) {
            return preg_match($regex, $path);
        });
    }

    public function filterByDate($date)
    {
        $comparator = new DateComparator($date);

        return $this->filter(function($path) use ($comparator) {
            return $comparator->test(File::lastModified($path));
        });
    }

    /**
     * Remove hidden files (files starting with .)
     *
     * @return static
     */
    public function removeHidden()
    {
        return $this->reject(function($path) {
            return Str::startsWith(pathinfo($path)['basename'], '.');
        });
    }

    /**
     * Sort by multiple fields
     *
     * Accepts a string like "title:desc|foo:asc"
     * The keys are optional. "title:desc|foo" is fine.
     *
     * @param string $sort
     * @return static
     */
    public function multisort($sort)
    {
        $sorts = explode('|', $sort);

        $arr = $this->all();

        usort($arr, function ($a, $b) use ($sorts) {
            foreach ($sorts as $sort) {
                $bits = explode(':', $sort);
                $sort_by = $bits[0];
                $sort_dir = array_get($bits, 1);

                list($one, $two) = $this->getSortableValues($sort_by, $a, $b);

                $result = Helper::compareValues($one, $two);

                if ($result !== 0) {
                    return ($sort_dir === 'desc') ? $result * -1 : $result;
                }
            }

            return 0;
        });

        return new static($arr);
    }

    /**
     * Get the values from two files to be sorted against each other
     *
     * @param string  $sort  The field to be searched
     * @param array   $a     The first file
     * @param array   $b     The second file
     * @return array
     */
    protected function getSortableValues($sort, $a, $b)
    {
        switch ($sort) {
            case 'type':
                $one = File::extension($a);
                $two = File::extension($b);
                break;

            case 'size':
                $one = File::size($a);
                $two = File::size($b);
                break;

            case 'last_modified':
                $one = File::lastModified($a);
                $two = File::lastModified($b);
                break;

            case 'random':
                $one = rand();
                $two = rand();
                break;

            default:
                $one = $a;
                $two = $b;
                break;
        }

        return [$one, $two];
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach ($this->items as $path) {
            $pathinfo = pathinfo($path);

            $size = File::size($path);
            $kb = number_format($size / 1024, 2);
            $mb = number_format($size / 1048576, 2);
            $gb = number_format($size / 1073741824, 2);

            $data[] = [
                'file'           => URL::format($path), // Todo: This will only work when using the local file adapter
                'filename'       => $pathinfo['filename'],
                'extension'      => array_get($pathinfo, 'extension'),
                'basename'       => array_get($pathinfo, 'basename'),
                'size'           => File::sizeHuman($path),
                'size_bytes'     => $size,
                'size_kilobytes' => $kb,
                'size_megabytes' => $mb,
                'size_gigabytes' => $gb,
                'size_b'         => $size,
                'size_kb'        => $kb,
                'size_mb'        => $kb,
                'size_gb'        => $kb,
                'is_file'        => File::isImage($path),
                'last_modified'  => Carbon::createFromTimestamp(File::lastModified($path))
            ];
        }

        return $data;
    }
}
