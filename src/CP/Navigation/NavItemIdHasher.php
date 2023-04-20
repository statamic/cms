<?php

namespace Statamic\CP\Navigation;

class NavItemIdHasher
{
    public function appendHash($id)
    {
        return $id.':'.substr(str_shuffle(md5($id)), 0, 6);
    }
}
