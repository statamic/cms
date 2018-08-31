<?php

namespace Statamic\Addons;

use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\Extend\Fieldtype;

abstract class BundleFieldtype extends Fieldtype
{
    /**
     * Get the config fieldset's contents.
     *
     * An array of what would normally be written in YAML if it were a regular
     * fieldset. The resulting fieldset object created will be used by the
     * Fieldset Builder when configuring an instance of this fieldtype.
     *
     * @return array
     */
    public function fieldsetContents()
    {
        // In v2, fieldtypes would define their fieldtype 'fields' in their meta.yaml file
        // under the 'fieldtype_fields' key. In v3, the meta file has been removed in
        // favor of just returning an array from this method. For now, we'll just
        // keep the meta.yaml files and convert it to an array.
        $dir = pathinfo((new \ReflectionClass(static::class))->getFileName(), PATHINFO_DIRNAME);
        $contents = File::get($dir.'/meta.yaml', 'fieldtype_fields: []');
        $yaml = YAML::parse($contents);
        return ['fields' => array_get($yaml, 'fieldtype_fields', [])];
    }
}
