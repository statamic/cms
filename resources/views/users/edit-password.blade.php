@extends('statamic::layout')
@section('content-class', 'publishing')

@section('content')

    <div class="content-type-user">
        <div class="publish-form" id="publish-form">

            <form method="post" action="{{ route('user.password.update', $user->username()) }}">
                {!! csrf_field() !!}

                <div class="flex flex-wrap items-center w-full sticky" id="publish-controls">
                    <h1 class="w-full my-1 text-center lg:text-left lg:flex-1">
                        <span>{{ title_case(trans('cp.change_password')) }}{{ $notEditingOwnPassword ? ': ' . $user->username() : '' }}</span>
                    </h1>

                    <div class="controls flex flex-wrap items-center w-full lg:w-auto justify-center">
                        @if ($notEditingOwnPassword)
                            <user-options username="{{ $user->username() }}" status="{{ $user->status() }}" class="mr-2"></user-options>
                        @endif
                        <button type="submit" class="btn btn-primary">{{ trans('cp.save') }}</a>
                    </div>
                </div>

                <div class="w-full px-1 md:px-3">
                    <div class="flex justify-between">
                        <div class="w-full">
                            <div class="card p-0">
                                <div class="card-body">

                                    <div class="publish-fields">
                                        <div class="form-group p-2 m-0 text-fieldtype w-1/2">
                                            <div class="field-inner">
                                                <label>{{ trans_choice('cp.passwords', 1) }}</label>
                                                <input type="password" class="form-control type-text" name="password" id="password">
                                            </div>
                                        </div>

                                        <div class="form-group p-2 m-0 text-fieldtype w-1/2">
                                            <div class="field-inner">
                                                <label>{{ trans('cp.confirm_password') }}</label>
                                                <input type="password" class="form-control type-text" name="password_confirmation" id="password_confirmation">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
