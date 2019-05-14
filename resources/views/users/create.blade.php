@extends('statamic::layout')

@section('content')
    <user-wizard
        route="{{ cp_route('users.store') }}">
    </user-wizard>
@stop

@section('xcontent')
    <div class="max-w-lg mx-auto rounded shadow bg-white">
        <div class="max-w-md mx-auto px-2 py-6 text-center">
            <h1 class="mb-3">{{ __('Add a New User') }}</h1>
            <p class="text-grey">Users can be assigned to roles that customize their permissions, access, and abilities throughout the Control Panel.</p>
        </div>

        <div class="max-w-md mx-auto px-2 pb-7">
            <div>
                <label class="font-bold text-base mb-sm" for="name">{{ _('Email Address') }}*</label>
                <input type="text" class="input-text" autofocus tabindex="1">
            </div>

            <!-- show if not super -->
            <div class="mt-3">
                <label class="font-bold text-base mb-sm" for="name">{{ _('Role') }}*</label>
                <user_roles-fieldtype /></user_roles-fieldtype>
            </div>

            <div class="flex items-center mt-3">
                <toggle-fieldtype name="super" :value="false"></toggle-fieldtype>
                <div class="ml-2 text-sm font-medium">Super Admin</div>
            </div>

            <!-- show if not generating password -->
            <div class="mt-3 hidden">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Password')}}</label>
                <input type="text"  class="input-text" tabindex="2">
            </div>

            <div class="flex items-center mt-3">
                <toggle-fieldtype name="generate_password" :value="true"></toggle-fieldtype>
                <div class="ml-2 text-sm font-medium">Automatically generate password</div>
            </div>

            <div class="flex items-center mt-2">
                <toggle-fieldtype name="send_email" :value="false"></toggle-fieldtype>
                <div class="ml-2 text-sm font-medium">Send email invite</div>
            </div>

            <div class="flex items-center mt-2">
                <toggle-fieldtype name="send_email" :value="false"></toggle-fieldtype>
                <div class="ml-2 text-sm font-medium">Ask for a password change at the next sign-in</div>
            </div>
        </div>
        <div class="border-t p-2 flex items-center justify-center">
                <button tabindex="3" class="btn mx-2 w-32">
                    {{ __('Cancel')}}
                </button>
                <button tabindex="4" class="btn-primary mx-2 w-32">
                    {{ __('Add New User')}}
                </button>
        </div>
    </div>
@stop

@section('nontent')

    <user-publish-form
        action="{{ cp_route('users.store') }}"
        method="post"
        :initial-fieldset="{{ json_encode($blueprint->toPublishArray()) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        inline-template
    >
        <div>
            <div class="flex mb-3">
                <h1 class="flex-1">
                    <a href="{{ cp_route('users.index')}}">{{ __('Users') }}</a>
                    @svg('chevron-right')
                    {{ __('Create User') }}
                </h1>
                <a href="" class="btn btn-primary" @click.prevent="save">{{ __('Save') }}</a>
            </div>

            <publish-container
                v-if="fieldset"
                name="base"
                :fieldset="fieldset"
                :values="values"
                :meta="meta"
                :errors="errors"
                @updated="values = $event"
            >
                <div slot-scope="{ }">
                    <publish-sections></publish-sections>
                </div>
            </publish-container>
        </div>
    </user-publish-form>

@endsection
