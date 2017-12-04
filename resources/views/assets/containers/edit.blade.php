@extends('layout')

@section('content')

    <asset-container-form :is-new="false"
                          :container='{!! json_encode($container->toArray()) !!}'>
    </asset-container-form>

@endsection
