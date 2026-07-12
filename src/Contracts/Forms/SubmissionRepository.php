<?php

namespace Statamic\Contracts\Forms;

interface SubmissionRepository
{
    public function all();

    public function whereForm(string $handle);

    public function whereInForm(array $handles);

    public function find($id);

    public function make();

    public function query();

    public function save($entry);

    public function delete($entry);
}
