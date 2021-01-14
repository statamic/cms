---
title: 'GraphQL API'
intro: 'The GraphQL API is a **read-only** API for delivering content from Statamic to your frontend, external apps, SPAs, and numerous other possible sources. Content is delivered as JSON data.'
stage: 1
pro: true
id: fc564ddf-80c1-4d87-8675-4a41f13c7774
---

## Interfaces

Statamic will provide "interface" types, which describe more generic items. For instance, an `EntryInterface` exists for all
entries, which would provide fields like `id`, `slug`, `status`, `title`, and so on.

In addition to the interfaces, Statamic will provide implementations of them, which would come from the blueprints.

For example, if you had a collection named `pages`, and it had blueprints of `page` and `home`, you would find `Entry_Pages_Page`
and `Entry_Pages_Home` types. These implementations would provide fields specific to the blueprint, like `subtitle`, `content`, etc.

```graphql
{
    entries {
        id
        title
        ... on Entry_Pages_Page {
            subtitle
            content
        }
        ... on Entry_Pages_Home {
            hero_intro
            hero_image
        }
    }
}
```

## Queries

Statamic has a number of root level queries you can perform to get data.

You can read about the [available queries](#available-queries) further down the page,
but know that you can perform more than one query at a time. They just need to be at the top level of your GraphQL query body.

For example, the following would perform both `entries` and `collections` queries

```graphql
{
    entries {
        # ...
    }
    collections {
        # ...
    }
}
```

The response will contain the results of both queries:

```json
{
    "entries": { /* ... */ },
    "collections": { /* ... */ },
}
```

Note that you can even perform the same query multiple times. If you want to do this, you should use aliases:

```graphql
{
    home: entry(id: "home") {
        title
    }
    contact: entry(id: "contact") {
        title
    }
}
```

```json
{
    "home": { /* ... */ },
    "blog": { /* ... */ },
}
```

## Available Queries

- [Ping](#ping-query)
- [Collections](#collections-query)
- [Collection](#collection-query)
- [Entries](#entries-query)
- [Entry](#entry-query)
- [Asset Containers](#asset-containers-query)
- [Asset Container](#asset-container-query)
- [Assets](#assets-query)
- [Asset](#asset-query)
- [Taxonomies](#taxonomies-query)
- [Taxonomy](#taxonomy-query)
- [Terms](#terms-query)
- [Term](#term-query)
- [Global Sets](#global-sets-query)
- [Global Set](#global-set-query)
- [Navs](#navs-query)
- [Nav](#nav-query)

### Ping {#ping-query}

Used for testing that your connection works. If you send a query of `{ping}`, you should receive `{"data": {"ping": "pong"}}`.

```graphql
{
    ping
}
```

```json
{
    "data": {
        "ping": "pong"
    }
}
```

### Collections {#collections-query}

Used for querying collections.

Returns a list of [Collection](#collection-type) types.

```graphql
{
    collections {
        handle
        title
    }
}
```

```json
{
    "collections": [
        { "handle": "blog", "title": "Blog Posts" },
        { "handle": "events", "title": "Events" },
    ]
}
```

### Collection {#collection-query}

Used for querying a single collection.

Returns a [Collection](#collection-type) type.

```graphql
{
    collection(handle: "blog") {
        handle
        title
    }
}
```

```json
{
    "collections": {
        "handle": "blog", 
        "title": "Blog Posts"
    }
}
```

### Entries {#entries-query}

Used for querying multiple entries.

Returns a [paginated](#pagination) list of [EntryInterface](#entry-interface) types.

| Argument | Type | Description |
|----------|------|-------------|
| `collection` | `[String]` | Narrows down the results by entries in one or more collections.
| `limit` | `Int` | The number of results to be shown per paginated page.
| `page` | `Int` | The paginated page to be shown. Defaults to `1`.
| `filter` | `JsonArgument` | Narrows down the results based on [filters](#filters).
| `sort` | `[String]` | [Sorts](#sorting) the results based on one or more fields and directions.

Example query and response:

```graphql
{
    entries {
        current_page
        data {
            id
            title
        }
    }
}
```

```json
{
    "entries": {
        "current_page": 1,
        "data": [
            { "id": 1, "title": "First Entry" },
            { "id": 2, "title": "Second Entry" }
        ]
    }
}
```

### Entry {#entry-query}

Used for querying a single entry.

```graphql
{
    entry(id: 1) {
        id
        title
    }
}
```

```json
{
    "entry": {
        "id": 1, 
        "title": "First Entry"
    }
}
```

### Asset Containers {#asset-containers-query}

Used for querying asset containers.

```graphql
{
    assetContainers {
        handle
        title
    }
}
```

```json
{
    "assetContainers": [
        { "handle": "images", "title": "Images" },
        { "handle": "documents", "title": "Documents" },
    ]
}
```

### Asset Container {#asset-container-query}

Used for querying a single asset container.

Returns an [AssetContainer](#asset-container-type) type.

```graphql
{
    assetContainer(handle: "images") {
        handle
        title
    }
}
```

```json
{
    "assetContainer": {
        "handle": "images", 
        "title": "Images"
    }
}
```

| Argument | Type | Description |
|----------|------|-------------|
| `handle` | `String!` | Specifies which asset container to retrieve.

### Assets {#assets-query}

Used for querying multiple assets of an asset container.

Returns a [paginated](#pagination) list of [AssetInterface](#asset-interface) types.

| Argument | Type | Description |
|----------|------|-------------|
| `container` | `String!` | Specifies which asset container to query.
| `limit` | `Int` | The number of results to be shown per paginated page.
| `page` | `Int` | The paginated page to be shown. Defaults to `1`.
| `sort` | `[String]` | [Sorts](#sorting) the results based on one or more fields and directions.

Example query and response:

```graphql
{
    assets(container: "images") {
        current_page
        data {
            url
        }
    }
}
```

```json
{
    "entries": {
        "current_page": 1,
        "data": [
            { "url": "/assets/images/001.jpg" },
            { "url": "/assets/images/002.jpg" },
        ]
    }
}
```

### Asset {#asset-query}

Used for querying a single asset.

```graphql
{
    asset(id: 1) {
        id
        title
    }
}
```

```json
{
    "asset": {
        "id": 1, 
        "title": "First Entry"
    }
}
```

You can either query by `id`, or by `container` and `path` together.

| Argument | Type | Description |
|----------|------|-------------|
| `id` | `String` | The ID of the asset. If you use this, you don't need `container` or `path`.
| `container` | `String` | The container to look for the asset. You must also provide the `path`.
| `path` | `String` | The path to the asset, relative to the container. You must also provide the `container`.

### Taxonomies {#taxonomies-query}

Used for querying taxonomies.

```graphql
{
    taxonomies {
        handle
        title
    }
}
```

```json
{
    "taxonomies": [
        { "handle": "tags", "title": "Tags" },
        { "handle": "categories", "title": "Categories" },
    ]
}
```

### Taxonomy {#taxonomy-query}

Used for querying a single taxonomy.

```graphql
{
    taxonomy(handle: "tags") {
        handle
        title
    }
}
```

```json
{
    "taxonomy": {
        "handle": "tags", 
        "title": "Tags"
    }
}
```

### Terms {#terms-query}

Used for querying multiple taxonomy terms.

Returns a [paginated](#pagination) list of [TermInterface](#term-interface) types.

| Argument | Type | Description |
|----------|------|-------------|
| `taxonomy` | `[String]` | Narrows down the results by terms in one or more taxonomies.
| `limit` | `Int` | The number of results to be shown per paginated page.
| `page` | `Int` | The paginated page to be shown. Defaults to `1`.
| `filter` | `JsonArgument` | Narrows down the results based on [filters](#filters).
| `sort` | `[String]` | [Sorts](#sorting) the results based on one or more fields and directions.

Example query and response:

```graphql
{
    terms {
        current_page
        data {
            id
            title
        }
    }
}
```

```json
{
    "terms": {
        "current_page": 1,
        "data": [
            { "id": "tags::one", "title": "Tag One" },
            { "id": "tags::two", "title": "Tag Two" }
        ]
    }
}
```

### Term {#term-query}

Used for querying a single taxonomy term.

```graphql
{
    term(id: "tags::one") {
        id
        title
    }
}
```

```json
{
    "term": {
        "id": "tags::one", 
        "title": "Tag One""
    }
}
```

### Global Sets {#global-sets-query}

Used for querying multiple global sets.

Returns a list of [GlobalSetInterface](#global-set-interface) types.

| Argument | Type | Description |
|----------|------|-------------|
| `taxonomy` | `[String]` | Narrows down the results by terms in one or more taxonomies.
| `limit` | `Int` | The number of results to be shown per paginated page.
| `page` | `Int` | The paginated page to be shown. Defaults to `1`.
| `filter` | `JsonArgument` | Narrows down the results based on [filters](#filters).
| `sort` | `[String]` | [Sorts](#sorting) the results based on one or more fields and directions.

Example query and response:

```graphql
{
    globalSets {
        title
        handle
        ... on GlobalSet_Social {
            twitter
        }
        ... on GlobalSet_Company {
            company_name
        }
    }
}
```

```json
{
    "globalSets": [
        { "handle": "social", "twitter": "@statamic" },
        { "handle": "company", "company_name": "Statamic" },
    ]
}
```

### Global Set {#global-set-query}

Used for querying a single global set.

```graphql
{
    globalSet(handle: "social") {
        title
        handle
        ... on GlobalSet_Social {
            twitter
        }
    }
}
```

```json
{
    "globalSet": {
        "title": "Social",
        "handle": "social",
        "twitter": "@statamic",
    }
}
```

### Navs {#navs-query}

Used for querying Navs.

```graphql
{
    navs {
        handle
        title
    }
}
```

```json
{
    "navs": [
        { "handle": "header_links", "title": "Header Links" },
        { "handle": "footer_links", "title": "Footer Links" },
    ]
}
```

### Nav {#nav-query}

Used for querying a single Nav.

```graphql
{
    nav(handle: "footer") {
        handle
        title
    }
}
```

```json
{
    "nav": {
        "handle": "footer", 
        "title": "Footer Links"
    }
}
```

## Types

- [EntryInterface](#entry-interface)
- [Collection](#collection-type)
- [CollectionStructure](#collection-structure-type)
- [TreeBranch](#tree-branch-type)
- [PageInterface](#page-interface)
- [TermInterface](#term-interface)
- [AssetInterface](#asset-interface)
- [GlobalSetInterface](#global-set-interface)

### EntryInterface {#entry-interface}

| Field | Type | Description |
|-------|------|-------------|
| `id` | `ID!` | 
| `title` | `String!` |

Each `EntryInterface` will also have implementations for each collection/blueprint combination.

You will need to query the implementations using fragments in order to get blueprint-specific fields.

```graphql
{
    entries {
        id
        title
        ... on Entry_Blog_Post {
            intro
            content
        }
        ... on Entry_Blog_ArtDirected_Post {
            hero_image
            content
        }
    }
}
```

The fieldtypes will define their types. For instance, a text field will be a `String`, a [grid](#grid-fieldtype) field will expose a list of `GridItem` types.

### Collection {#collection-type}

| Field | Type | Description |
|-------|------|-------------|
| `handle` | `String!` | 
| `title` | `String!` |
| `structure` | [`CollectionStructure`](#collection-structure-type) | If the collection is structured (e.g. a "pages" collection), you can use this to query its tree.

### CollectionStructure {#collection-structure-type}

| Field | Type | Description |
|-------|------|-------------|
| `handle` | `String!` | 
| `title` | `String!` |
| `tree` | [[`TreeBranch`](#tree-branch-type)] | A list of tree branches.

### TreeBranch {#tree-branch-type}

Represents a branch within a (collection or nav) structure's tree.

| Field | Type | Description |
|-------|------|-------------|
| `depth` | `Int!` | The nesting level of the current branch.
| `page` | [`PageInterface`](#page-interface) | Contains page's fields.
| `children` | [[`TreeBranch`](#tree-branch-type)] | A list of tree branches.

> Note: it's not possible to do recursive queries in GraphQL, so if you want to get multiple levels of child branches,
> take a look at a workaround in [recursive tree branches](#recursive-tree-branches) below.

### PageInterface {#page-interface}

A "page" within a structure tree could be a basic text/url node, or it could be a reference to an entry.

When it's a basic node, you'll have access to all the [EntryInterface](#entry-interface)'s basic fields like `title` and `url`.

When it's a reference to an entry, you'll _also_ be able to query any of the entry's implementations. However, instead of the `Entry_` prefix, you'll need to use an `EntryPage_` prefix.

### TermInterface {#term-interface}

| Field | Type | Description |
|-------|------|-------------|
| `id` | `ID!` | 
| `title` | `String!` |
| `slug` | `String!` |

Each `TermInterface` will also have implementations for each taxonomy/blueprint combination.

You will need to query the implementations using fragments in order to get blueprint-specific fields.

```graphql
{
    terms {
        id
        title
        ... on Term_Tags_RegularTag {
            content
        }
        ... on Term_Tags_SpecialTag {
            how_special
            content
        }
    }
}
```

The fieldtypes will define their types. For instance, a text field will be a `String`, a [grid](#grid-fieldtype) field will expose a list of `GridItem` types.

### AssetInterface {#asset-interface}

| Field | Type | Description |
|-------|------|-------------|
| `path` | `String!` | The path to the asset.

Each `AssetInterface` will also have an implementation for each asset container's blueprint.

You will need to query the implementations using fragments in order to get blueprint-specific fields.

```graphql
{
    entries {
        path
        ... on Asset_Images {
            alt
        }
    }
}
```

The fieldtypes will define their types. For instance, a text field will be a `String`, a [grid](#grid-fieldtype) field will expose a list of `GridItem` types.

### GlobalSetInterface {#global-set-interface}

| Field | Type | Description |
|-------|------|-------------|
| `handle` | `String!` | The handle of the set.
| `title` | `String!` | The title of the set.

Each `GlobalSetInterface` will also have an implementation for each set's blueprint.

> Note that while Statamic doesn't enfore a blueprint for a globals (see [Blueprint is Optional](/blueprints#blueprint-is-optional)), it is required within the context of GraphQL. Fields that haven't been explicitly added to a blueprint will not be available.

You will need to query the implementations using fragments in order to get blueprint-specific fields.

```graphql
{
    globalSets {
        handle
        ... on GlobalSet_Social {
            twitter
        }
    }
}
```

The fieldtypes will define their types. For instance, a text field will be a `String`, a [grid](#grid-fieldtype) field will expose a list of `GridItem` types.

## Filtering

You can filter the results of listing queries (like `entries`) using the `filter` argument. This argument accepts a JSON object containing different
[conditions](/conditions).

```graphql
{
    entries(filter: {
        title: { contains: "rad", ends_with: "!" }
    }) {
        data {
            title
        }
    }
}
```

```json
{
    "data": [
        { "title": "That was so rad!" },
        { "title": "I wish I was as cool as Daniel Radcliffe!" },
    ]
}
```

If you only need to do a simple "equals" condition, then you can use a string and omit the condition name, like the `rating` here:

```graphql
{
    entries(filter: {
        title: { contains: "rad" }
        rating: 5
    }) {
        # ...
    }
```

If you need to use the same condition on the same field more than once, you can use the array syntax:

```graphql
{
    entries(filter: {
        title: [
            { contains: "rad" },
            { contains: "awesome" },
        ]
    }) {
        # ...
    }
```

## Sorting

You can sort the results of listing queries (like `entries`) on one or multiple fields, in any direction.

```graphql
{
    entries(sort: "title") {
        # ...
    }
```

```graphql
{
    entries(sort: "title desc") {
        # ...
    }
```

```graphql
{
    entries(sort: ["price desc", "title asc"]) {
        # ...
    }
```

## Pagination

Some queries (like [entries](#entries-query)) will provide their results using pagination.

In a paginated response, you will find the actual items within a `data` key.

By default there will be `1000` per page. You can change this using a `limit` argument.
You can specify the current paginated page using the `page` argument.

```graphql
{
    entries(limit: 15, page: 2) {
        current_page
        has_more_pages
        data {
            # ...
        }
    }
}
```

| Field | Type | Description | 
|-------|------|-------------|
| `data` | [mixed] | A list of items on the current page. In an `entries` query, there will be `EntryInterface` types, etc.
| `total` | `Int!` | Number of total items selected by the query.
| `per_page` | `Int!` | Number of items returned per page.
| `current_page` | `Int!` | Current page of the cursor.
| `from` | `Int` | Number of the first item returned.
| `to` | `Int` | Number of the last item returned.
| `last_page` | `Int!` | The last page (number of pages).
| `has_more_pages` | `Boolean!` | Determines if cursor has more pages after the current page.


## Fieldtypes

### Replicator

Replicator fields require that you query each set using a separate fragment.

The fragments are named after your configured sets using StudlyCased field and set handles. e.g. `Set_{ReplicatorFieldName}_{SetHandle}`

```yaml
fields:
  -
    handle: content_blocks
    field:
      type: replicator
      sets:
        image:
          fields:
            -
              handle: image
              type: assets
              max_files: 1
        pull_quote:
          fields:
            -
              handle: quote
              field:
                type: textarea
            -
              handle: author
              field:
                type: text  
```

```graphql
{
    content_blocks {
        ... on Set_ContentBlocks_Image {
            type
            image
        }
        ... on Set_ContentBlocks_PullQuote {
            type
            quote
            author
        }
    }
}
```

### Bard

Bard fields work the same as Replicator, except that you also have an additional `BardText_{FieldHandle}` for the text fragment.

```graphql
{
    content_blocks {
        ... on BardText_ContentBlocks {
            type
            text
        }
        ... on Set_ContentBlocks_Image {
            type
            image
        }
        ... on Set_ContentBlocks_PullQuote {
            type
            quote
            author
        }
    }
}
```

### Grid

Grid fields can be queried with no extra requirements. You can just use the nested field handles.

```graphql
{
    cars {
        make
        model
    }
}
```

## Recursive Tree Branches

Often, when dealing with navs, you need to recursively output all the child branches. For example, when using the `nav` tag in Antlers, you might do something like this:

```
<ul>
{{ nav }}
    <li>
        <a href="{{ url }}">{{ title }}</a>
        {{ if children }}
            <ul>{{ *recursive children* }}</ul>
        {{ /if }}
    </li>
{{ /nav }}
</ul>
```

In GraphQL, it's not possible to perform recursive queries like that. You'll need to explicitly query each level:

```graphql
{
    nav(handle: "links") {
        tree {
            page {
                title
                url
            }
            children {
                page {
                    title
                    url
                }
                children {
                    page {
                        title
                        url
                    }
                }
            }
        }
    }
}
```

In this example, if you wanted anything more than `title` and `url`, you'd need to add them to each level.

This can quickly become tedious and is very repetitive, so here's a workaround using fragments. If you wanted to add more fields, you only need to do it one spot. If you want to query more levels, you can just increase the nesting level of the recursive fragment.

```graphql
{
    nav(handle: "links") {
        tree {
            ...Fields
            ...RecursiveChildren
        }
    }
}

fragment Fields on TreeBranch {
    depth
    page {
        title
        url
        # any other fields you want for each branch
    }
}

fragment RecursiveChildren on TreeBranch {
    children {
        ...Fields
        children {
            ...Fields
            children {
                ...Fields
                ## just keep repeating this as deep as necessary
            }
        }
    }
}
```

Hat tip to Hash Interactive for their [blog post](https://hashinteractive.com/blog/graphql-recursive-query-with-fragments/) on this technique.

## Custom Fieldtypes

A fieldtype can define what GraphQL type will be used. By default, all fieldtypes will return strings.

```php
use GraphQL\Type\Definition\Type;

public function toGqlType()
{
    return Type::string();
}
```

You're free to return an array with a more complicated structure in order to provide arguments, etc.

```php
use GraphQL\Type\Definition\Type;

public function toGqlType()
{
    return [
        'type' => Type::string(),
        'args' => [
            //
        ]
    ];
}
```

If you need to register any types, the fieldtype can do that in the `addGqlTypes` method:

```php
public function addGqlTypes()
{
    // A class that extends Rebing\GraphQL\Support\Type
    $type = MyType::class; // or `new MyType;`

    GraphQL::addType($type);
}
```

## Laravel Package

Under the hood, Statamic uses the [rebing/graphql-laravel](https://github.com/rebing/graphql-laravel) package.

By default, the integration should feel seamless and you won't even know another package is being used. Statamic will 
perform the following automatic configuration of this package:

- Setting up the `default` schema to Statamic's.
- Disabling the `/graphiql` route (since we have our own inside the Control Panel)

However, you're free to use this package on its own, as if you've installed it into a standalone Laravel application.

If Statamic detects that you've published the package's config file (located at `config/graphql.php`), it will assume you're trying to use it manually and will
avoid doing the automatic setup steps mentioned above.

If you'd like to use Statamic's GraphQL schema in within the config file (maybe you want a different default, and want Statamic's one at `/graphql/statamic`)
you can use the `DefaultSchema` class.

```php
[
    'schemas' => [
        'statamic' => \Statamic\GraphQL\DefaultSchema::config()
    ]
]
```
