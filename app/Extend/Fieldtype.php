<?php

namespace Statamic\Extend;

use Statamic\API\Fieldset;
use Statamic\API\Path;
use Statamic\API\Helper;
use Statamic\API\Str;

/**
 * Control panel fieldtype
 */
class Fieldtype
{
    /**
     * The configuration of the field from within a fieldset
     * @var array
     */
    protected $field_config;

    /**
     * The data contained in this field
     * @var mixed
     */
    protected $field_data;

    /**
     * The name of this fieldtype in snake case format, if desired.
     * @var string
     */
    protected $snake_name;

    /**
     * Whether this is a config field
     * @var bool
     */
    public $is_config = false;

    /**
     * Fieldtype categories
     * @var array
     */
    public $category = ['text'];

    /**
     * Whether this fieldtype should appear in the selector
     * @var bool
     */
    public $selectable = true;

    public function setFieldConfig($config)
    {
        $this->field_config = $config;

        return $this;
    }

    /**
     * Get the field config
     *
     * @param string|null $key
     * @param string|null $default
     * @return mixed
     */
    public function getFieldConfig($key = null, $default = null)
    {
        if (! $key) {
            return $this->field_config;
        }

        return array_get($this->field_config, $key, $default);
    }

    /**
     * Gets the field's name
     *
     * @return mixed
     */
    public function getName()
    {
        return array_get($this->field_config, 'name');
    }

    public function getHandle()
    {
        return app('statamic.fieldtypes')->flip()->get(get_class($this));
    }

    /**
     * Get the "display" name of the fieldtype.
     *
     * This will be used in things like a dropdown of all fieldtypes.
     *
     * @return string
     */
    public function getFieldtypeName()
    {
        $addon = $this->getAddonClassName();

        $classBasedName = Str::removeRight(last(explode('\\', get_class($this))), 'Fieldtype');

        return ($classBasedName === $addon)
            ? $classBasedName
            : $classBasedName . ' - ' . $addon;
    }

    public function getIcon()
    {
        return $this->getHandle();
    }

    /**
     * Retrieves a parameter or config value
     *
     * @param string|array $keys Keys of parameter to return
     * @param null         $default
     * @return mixed
     */
    public function get($keys, $default = null)
    {
        return Helper::pick(
            $this->getParam($keys, $default),
            $default
        );
    }

    /**
     * Retrieves a parameter
     *
     * @param string|array $keys Keys of parameter to return
     * @param mixed $default  Default value to return if not set
     * @return mixed
     */
    public function getParam($keys, $default = null)
    {
        if (! is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $key) {
            if (isset($this->field_config[$key])) {
                return $this->field_config[$key];
            }
        }

        return $default;
    }

    /**
     * Same as $this->getParam(), but treats as a boolean
     *
     * @param string|array $keys
     * @param null         $default
     * @return bool
     */
    public function getParamBool($keys, $default = null)
    {
        return bool($this->getParam($keys, $default));
    }

    /**
     * Same as $this->getParam(), but treats as an integer
     *
     * @param string|array $keys
     * @param null         $default
     * @return int
     */
    public function getParamInt($keys, $default = null)
    {
        return int($this->getParam($keys, $default));
    }

    /**
     * Allows processing of the data before being used
     *
     * @param mixed $data  Data from the content
     * @return mixed
     */
    public function preProcess($data)
    {
        return $data;
    }

    /**
     * Allows processing of the data upon saving
     *
     * @param mixed $data  Data from the publish page form
     * @return mixed
     */
    public function process($data)
    {
        return $data;
    }

    /**
     * The fieldtype's default/blank value
     *
     * @return null
     */
    public function blank()
    {
        return null;
    }

    /**
     * Validation rules
     *
     * @return null|string
     */
    public function rules()
    {
        return [];
    }

    public function extraRules()
    {
        return [];
    }

    public function extraAttributes()
    {
        return [];
    }

    public function getConfigFieldset()
    {
        $fieldset = Fieldset::create('config', $this->fieldsetContents());
        $fieldset->type('fieldtype');

        return $fieldset;
    }

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
        return [];
    }

    /**
     * Can this field have validation rules?
     *
     * @return bool
     */
    public function canBeValidated()
    {
        return true;
    }

    /**
     * Can this field be localized?
     *
     * @return bool
     */
    public function canBeLocalized()
    {
        return true;
    }

    /**
     * Can this field have a default value?
     *
     * @return bool
     */
    public function canHaveDefault()
    {
        return true;
    }
}
