@extends('statamic::layout')

@section('content')

    <user-publish-form
        action="{{ cp_route('users.update', $user->id()) }}"
        method="patch"
        :initial-fieldset="{{ json_encode($user->blueprint()->toPublishArray()) }}"
        :initial-values="{{ json_encode($values) }}"
        inline-template
    >
        <div>
            <div class="flex mb-3">
                <h1 class="flex-1">
                    <a href="{{ cp_route('users.index')}}">{{ __('Users') }}</a>
                    @svg('chevron-right')
                    {{ $user->username() }}
                </h1>

                {{-- TODO: @if(can edit this user's password) --}}
                @if (true)
                    <change-password
                        save-url="{{ cp_route('users.password.update', $user->id()) }}"
                    ></change-password>
                @endcan

                <a href="" class="btn btn-primary ml-2" @click.prevent="save">{{ __('Save') }}</a>
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
                    <div class="alert alert-danger mb-2" v-if="error" v-text="error" v-cloak></div>
                    <publish-sections></publish-sections>
                </div>
            </publish-container>
        </div>
    </user-publish-form>

@endsection





