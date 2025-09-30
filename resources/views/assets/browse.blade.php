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
            class="starting-style-transition"
        >
            <template #initializing>
                <!-- Toolbar skeleton -->
                <div class="flex justify-between">
                    <ui-skeleton class="h-9 w-95 mb-3" />
                    <ui-skeleton class="h-9 w-10 mb-3" />
                </div>
                <!-- Assets grid skeleton -->
                <div class="flex justify-between">
                    <ui-skeleton class="h-100 w-full" />
                </div>
            </template>
        </asset-manager>

        <x-statamic::docs-callout
            topic="{{ __('Assets') }}"
            url="{{ Statamic::docsUrl('assets') }}"
            class="starting-style-transition"
        />
    </div>
@endsection
