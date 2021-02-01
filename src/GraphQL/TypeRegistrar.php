<?php

namespace Statamic\GraphQL;

use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\AssetContainerType;
use Statamic\GraphQL\Types\AssetInterface;
use Statamic\GraphQL\Types\CollectionStructureType;
use Statamic\GraphQL\Types\CollectionType;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\GlobalSetInterface;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\GraphQL\Types\LabeledValueType;
use Statamic\GraphQL\Types\NavType;
use Statamic\GraphQL\Types\PageInterface;
use Statamic\GraphQL\Types\RoleType;
use Statamic\GraphQL\Types\SiteType;
use Statamic\GraphQL\Types\TableRowType;
use Statamic\GraphQL\Types\TaxonomyType;
use Statamic\GraphQL\Types\TermInterface;
use Statamic\GraphQL\Types\TreeBranchType;
use Statamic\GraphQL\Types\UserGroupType;
use Statamic\GraphQL\Types\UserType;

class TypeRegistrar
{
    private $registered = false;

    public function register()
    {
        if ($this->registered) {
            return;
        }

        GraphQL::addType(JsonArgument::class);
        GraphQL::addType(SiteType::class);
        GraphQL::addType(LabeledValueType::class);
        GraphQL::addType(CollectionType::class);
        GraphQL::addType(CollectionStructureType::class);
        GraphQL::addType(TaxonomyType::class);
        GraphQL::addType(AssetContainerType::class);
        GraphQL::addType(NavType::class);
        GraphQL::addType(TreeBranchType::class);
        GraphQL::addType(UserType::class);
        GraphQL::addType(UserGroupType::class);
        GraphQL::addType(RoleType::class);
        GraphQL::addType(TableRowType::class);
        PageInterface::addTypes();
        EntryInterface::addTypes();
        TermInterface::addTypes();
        AssetInterface::addTypes();
        GlobalSetInterface::addTypes();

        $this->registered = true;
    }
}
