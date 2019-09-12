@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('content-class', 'publishing')

@section('content')

    <script>
        <?php
            $taxonomies = isset($taxonomies) ? $taxonomies : [];
            $suggestions = isset($suggestions) ? $suggestions : [];
        ?>
        Statamic.Publish = {
            contentData: {!! json_encode($content_data) !!},
            taxonomies: {!! json_encode($taxonomies) !!},
            suggestions: {!! json_encode($suggestions) !!}
        };
    </script>

    <publish title="{{ $title }}"
             extra="{{ json_encode($extra) }}"
             :is-new="{{ $str::bool($is_new) }}"
             content-type="{{ $content_type }}"
             uuid="{{ $uuid }}"
             fieldset-name="{{ $fieldset }}"
             slug="{{ $slug }}"
             uri="{{ $uri }}"
             url="{{ $url }}"
             submit-url="{{ route("{$content_type}.save") }}"
             :status="{{ $str::bool($status) }}"
             locale="{{ $locale }}"
             locales="{{ json_encode($locales) }}"
             :is-default-locale="{{ $str::bool($is_default_locale) }}"
             title-display-name="{{ isset($title_display_name) ? $title_display_name : t('title') }}"
             :remove-title="true"
    ></publish>

@endsection
