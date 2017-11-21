<?php

namespace Statamic\Extend;

/**
 * Defines a fieldtype
 */
interface FieldtypeInterface
{
    /**
     * Allows processing of the data before being used
     *
     * @param mixed $data  Data from the content
     * @return mixed
     */
    public function preProcess($data);

    /**
     * Allows processing of the data upon saving
     *
     * @param mixed $data  Data from the publish page form
     * @return mixed
     */
    public function process($data);

    /**
     * The fieldtype's default/blank value
     *
     * @return mixed
     */
    public function blank();

    /**
     * Validation rules
     *
     * @return null|string
     */
    public function rules();

    /**
     * Can this field have validation rules?
     *
     * @return bool
     */
    public function canBeValidated();

    /**
     * Can this field be localized?
     *
     * @return bool
     */
    public function canBeLocalized();

    /**
     * Can this field have a default value?
     *
     * @return bool
     */
    public function canHaveDefault();
}
