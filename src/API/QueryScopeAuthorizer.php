<?php

namespace Statamic\API;

class QueryScopeAuthorizer extends FilterAuthorizer
{
    protected $configKey = 'allowed_query_scopes';
}
