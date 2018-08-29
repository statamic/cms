@extends('statamic::layout')

@section('content')

    <entry-publish-form
        action="{{ cp_route('collections.entries.update', [$entry->collectionName(), $entry->slug()]) }}"
        :initial-fieldset="{{ json_encode($entry->fieldset()->toPublishArray()) }}"
        :initial-values="{{ json_encode($data) }}"
        inline-template
    >
        <div>
            <div class="flex mb-3">
                <h1 class="flex-1">
                    <a href="{{ cp_route('collections.show', $entry->collectionName())}}">
                        {{ $entry->collection()->title() }}
                    </a>
                    @svg('new/chevron-right')
                    {{ $entry->get('title') }}
                </h1>
                <a href="" class="btn btn-primary" @click.prevent="save">{{ __('Save') }}</a>
            </div>

            <publish-container
                v-if="fieldset"
                name="base"
                :fieldset="fieldset"
                :values="initialValues"
                :errors="errors"
                @updated="values = $event"
            >
                <div slot-scope="{ }">
                    <publish-validation-errors></publish-validation-errors>
                    <publish-sections></publish-sections>
                </div>
            </publish-container>
        </div>
    </entry-publish-form>

@endsection





