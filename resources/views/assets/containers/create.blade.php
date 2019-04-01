@extends('statamic::layout')

@section('content')

    <form method="POST" action="{{ cp_route('asset-containers.store') }}">
        @csrf

        <div class="flex items-center mb-3">
            <h1 class="flex-1">
                <small class="subhead block">
                    <a href="{{ cp_route('assets.browse.index') }}">{{ __('Assets') }}</a>
                </small>
                {{ __('Create Asset Container') }}
            </h1>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>

        <div class="publish-fields p-0 card">

            <form-group
                handle="title"
                display="{{ __('Title') }}"
                instructions="{{ __('The proper name of your container.') }}"
                value="{{ old('title') }}"
                error="{{ $errors->first('title') }}"
                autofocus
            ></form-group>

            <form-group
                handle="handle"
                display="{{ __('Handle') }}"
                instructions="{{ __('How you will reference this container in configs, fields, etc.') }}"
                value="{{ old('handle') }}"
                error="{{ $errors->first('handle') }}"
            ></form-group>

            <form-group
                fieldtype="select"
                handle="disk"
                display="{{ __('Disk') }}"
                instructions="{{ __('The filesystem disk this container will use.') }}"
                value="{{ old('disk') }}"
                error="{{ $errors->first('disk') }}"
                :config="{{ json_encode(['options' => $disks]) }}"
            ></form-group>

            <form-group
                fieldtype="blueprints"
                handle="blueprint"
                display="{{ __('Blueprint') }}"
                instructions="{{ __('The blueprint that assets in this container will use.') }}"
                :value="{{ old('blueprint', '[]') }}"
                error="{{ $errors->first('blueprint') }}"
                :config="{ max_items: 1 }"
            ></form-group>

            <form-group
                fieldtype="toggle"
                handle="allow_uploads"
                display="{{ __('Allow Uploads') }}"
                instructions="{{ __('The ability to upload into this container.') }}"
                :value="{{ json_encode(old('allow_uploads', true)) }}"
                error="{{ $errors->first('allow_uploads') }}"
            ></form-group>

            <form-group
                fieldtype="toggle"
                handle="create_folders"
                display="{{ __('Create Folders') }}"
                instructions="{{ __('The ability to create folders within this container.') }}"
                :value="{{ json_encode(old('create_folders', true)) }}"
                error="{{ $errors->first('create_folders') }}"
            ></form-group>

        </div>
    </form>

@endsection
