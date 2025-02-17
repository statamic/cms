<?php

namespace Statamic\Exceptions;

use Illuminate\Database\MultipleRecordsFoundException as LaravelMultipleRecordsFoundException;

class MultipleRecordsFoundException extends LaravelMultipleRecordsFoundException
{
}
