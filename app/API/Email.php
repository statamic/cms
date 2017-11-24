<?php

namespace Statamic\API;

use Closure;

class Email
{
    /**
     * The email builder instance
     *
     * @return \Statamic\Email\Builder
     */
    private static function email()
    {
        return app('Statamic\Email\Builder');
    }

    /**
     * Begin building an email
     *
     * @return \Statamic\Email\Builder
     */
    public static function create()
    {
        return self::email();
    }

    /**
     * Begin building an email, populating the 'to' field
     *
     * @param string       $address
     * @param string|null  $name
     * @return \Statamic\Email\Builder
     */
    public static function to($address, $name = null)
    {
        return self::email()->to($address, $name);
    }

    /**
     * Begin building an email, populating the 'from' field
     *
     * @param string       $address
     * @param string|null  $name
     * @return \Statamic\Email\Builder
     */
    public static function from($address, $name = null)
    {
        return self::email()->from($address, $name);
    }

    /**
     * Begin building an email, populating the template
     *
     * @param string  $template
     * @return \Statamic\Email\Builder
     */
    public static function template($template)
    {
        return self::email()->template($template);
    }
}
