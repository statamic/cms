<?php

namespace Statamic\Query\Exceptions;

use Illuminate\Database\MultipleRecordsFoundException as LaravelMultipleRecordsFoundException;

class MultipleRecordsFoundException extends LaravelMultipleRecordsFoundException
{
}
