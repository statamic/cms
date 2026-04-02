<?php

namespace Statamic\Contracts\Revisions;

interface Revision
{
    public function id();

    public function message($message = null);

    public function attributes($attributes = null);

    public function attribute(string $attribute, $value);

    public function action($action = null);

    public function user($user = null);

    public function key($key = null);

    public function date($date = null);

    public function save();

    public function delete();

    public function toWorkingCopy(): Revision;

    public function isWorkingCopy(): bool;
}
