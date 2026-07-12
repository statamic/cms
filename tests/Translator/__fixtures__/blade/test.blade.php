{{ __('blade underscore single quote string') }}
{{ __('blade underscore single quote :param', ['param']) }}
{{ __("blade underscore double quote string") }}
{{ __("blade underscore double quote :param", ['param']) }}

{{ trans('blade trans single quote string') }}
{{ trans('blade trans single quote :param', ['param']) }}
{{ trans("blade trans double quote string") }}
{{ trans("blade trans double quote :param", ['param']) }}

{{ trans_choice('blade trans_choice single quote string', 2) }}
{{ trans_choice('blade trans_choice single quote :param', 2, ['param']) }}
{{ trans_choice("blade trans_choice double quote string", 2) }}
{{ trans_choice("blade trans_choice double quote :param", 2, ['param']) }}

{{ __('blade with/slash') }}
{{ __('blade with bracket(s)') }}
