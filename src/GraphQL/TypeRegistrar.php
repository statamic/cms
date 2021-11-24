<?php

namespace Statamic\GraphQL;

use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\ArrayType;
use Statamic\GraphQL\Types\AssetContainerType;
use Statamic\GraphQL\Types\AssetInterface;
use Statamic\GraphQL\Types\CodeType;
use Statamic\GraphQL\Types\CollectionStructureType;
use Statamic\GraphQL\Types\CollectionTreeBranchType;
use Statamic\GraphQL\Types\CollectionType;
use Statamic\GraphQL\Types\DateRangeType;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\FieldType;
use Statamic\GraphQL\Types\FormType;
use Statamic\GraphQL\Types\GlobalSetInterface;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\GraphQL\Types\LabeledValueType;
use Statamic\GraphQL\Types\NavTreeBranchType;
use Statamic\GraphQL\Types\NavType;
use Statamic\GraphQL\Types\PageInterface;
use Statamic\GraphQL\Types\RoleType;
use Statamic\GraphQL\Types\SiteType;
use Statamic\GraphQL\Types\TableRowType;
use Statamic\GraphQL\Types\TaxonomyType;
use Statamic\GraphQL\Types\TermInterface;
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

        GraphQL::addType(ArrayType::class);
        GraphQL::addType(CodeType::class);
        GraphQL::addType(JsonArgument::class);
        GraphQL::addType(DateRangeType::class);
        GraphQL::addType(SiteType::class);
        GraphQL::addType(LabeledValueType::class);
        GraphQL::addType(CollectionType::class);
        GraphQL::addType(CollectionStructureType::class);
        GraphQL::addType(TaxonomyType::class);
        GraphQL::addType(AssetContainerType::class);
        GraphQL::addType(NavType::class);
        GraphQL::addType(CollectionTreeBranchType::class);
        GraphQL::addType(NavTreeBranchType::class);
        GraphQL::addType(FormType::class);
        GraphQL::addType(UserType::class);
        GraphQL::addType(UserGroupType::class);
        GraphQL::addType(RoleType::class);
        GraphQL::addType(TableRowType::class);
        GraphQL::addType(PageInterface::class);
        GraphQL::addType(EntryInterface::class);
        GraphQL::addType(TermInterface::class);
        GraphQL::addType(AssetInterface::class);
        GraphQL::addType(GlobalSetInterface::class);
        GraphQL::addType(FieldType::class);

        PageInterface::addTypes();
        EntryInterface::addTypes();
        TermInterface::addTypes();
        AssetInterface::addTypes();
        GlobalSetInterface::addTypes();

        $this->registered = true;
    }
}
