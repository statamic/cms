@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Assets', $container['title']))

@section('content')
    <div v-cloak>
        <asset-manager
            :container="{{ json_encode($container) }}"
            :can-create-containers="{{ Statamic\Support\Str::bool($user->can('create', \Statamic\Contracts\Assets\AssetContainer::class)) }}"
            create-container-url="{{ cp_route('asset-containers.create') }}"
            initial-path="{{ $folder }}"
            initial-editing-asset-id="{{ $editing ?? null }}"
            :columns="{{ $columns->toJson() }}"
        >
            <template #initializing>
                <!-- Header Skeleton -->
                <div class="flex justify-between py-8">
                    <ui-skeleton class="h-8 w-95" />
                    <div class="flex gap-3">
                        <ui-skeleton class="h-10 w-26" />
                        <ui-skeleton class="h-10 w-36" />
                        <ui-skeleton class="h-10 w-20" />
                    </div>
                </div>
                <!-- Toolbar Skeleton -->
                <div class="flex justify-between py-3">
                    <ui-skeleton class="h-9 w-95" />
                    <ui-skeleton class="h-9 w-10" />
                </div>
                <!-- Assets grid Skeleton -->
                <div class="flex justify-between">
                    <ui-skeleton class="h-30 w-full" />
                </div>
            </template>
        </asset-manager>

        <x-statamic::docs-callout
            topic="{{ __('Assets') }}"
            url="{{ Statamic::docsUrl('assets') }}"
        />
    </div>
@endsection
