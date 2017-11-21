<?php

namespace Statamic\Contracts\Data\Users;

interface UserFactory
{
    /**
     * @return $this
     */
    public function create();

    /**
     * @return \Statamic\Contracts\Data\Users\User
     */
    public function get();

    /**
     * @param array $data
     * @return $this
     */
    public function with(array $data);

    /**
     * @param string $username
     * @return $this
     */
    public function username($username);

    /**
     * @param string $email
     * @return $this
     */
    public function email($email);
}
