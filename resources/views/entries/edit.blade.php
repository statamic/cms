@extends('statamic::layout')

@section('content')

    <entry-publish-form
        action="{{ $actions['update'] }}"
        method="patch"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        inline-template
    >
        <div>
            <div class="flex mb-3">
                <h1 class="flex-1">
                    <a href="{{ cp_route('collections.show', $collection->handle())}}">
                        {{ $collection->title() }}
                    </a>
                    @svg('chevron-right')
                    {{ $entry->get('title') }}
                </h1>
                <button
                    class="btn btn-primary"
                    :class="{ disabled: !canSave }"
                    :disabled="!canSave"
                    @click.prevent="save"
                    v-text="__('Save')"
                ></button>
            </div>

            <publish-container
                v-if="fieldset"
                ref="container"
                name="base"
                :fieldset="fieldset"
                :values="initialValues"
                :meta="initialMeta"
                :errors="errors"
                @updated="values = $event"
            >
                <div slot-scope="{ }">
                    <publish-sections></publish-sections>
                </div>
            </publish-container>
        </div>
    </entry-publish-form>

@endsection





