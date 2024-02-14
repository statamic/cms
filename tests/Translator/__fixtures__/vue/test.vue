<template>

    <div>
        {{ __('vue template single quote') }}
        {{ __n('vue template single quote :count', 3) }}
        {{ __n('vue template single quote :param', { param: 'test' }) }}

        {{ __("vue template double quote") }}
        {{ __n("vue template double quote :count", 3) }}
        {{ __n("vue template double quote :param", { param: 'test' }) }}

        {{ __(`vue template backtick quote`) }}
        {{ __n(`vue template backtick quote :count`, 3) }}
        {{ __n(`vue template backtick quote :param`, { param: 'test' }) }}

        {{ trans('vue template trans single quote') }}
        {{ trans('vue template trans single quote :param', { param: 'test' }) }}

        {{ trans("vue template trans double quote") }}
        {{ trans("vue template trans double quote :param", { param: 'test' }) }}

        {{ trans(`vue template trans backtick quote`) }}
        {{ trans(`vue template trans backtick quote :param`, { param: 'test' }) }}

        {{ trans_choice('vue template trans_choice single quote', 2) }}
        {{ trans_choice('vue template trans_choice single quote :param', 2, { param: 'test' }) }}

        {{ trans_choice("vue template trans_choice double quote", 2) }}
        {{ trans_choice("vue template trans_choice double quote :param", 2, { param: 'test' }) }}

        {{ trans_choice(`vue template trans_choice backtick quote`, 2) }}
        {{ trans_choice(`vue template trans_choice backtick quote :param`, 2, { param: 'test' }) }}

        {{ __('vue template with/slash') }}
        {{ __('vue template with bracket(s)') }}
    </div>

</template>

<script>
export default {

    data() {
        return [
            __('vue script single quote'),
            __n('vue script single quote :count', 2),
            __n('vue script single quote :param', { param: 'test' }),
            __("vue script double quote"),
            __n("vue script double quote :count", 2),
            __n("vue script double quote :param", { param: 'test' }),
            __(`vue script backtick quote`),
            __n(`vue script backtick quote :count`, 2),
            __n(`vue script backtick quote :param`, { param: 'test' }),

            trans('vue script trans single quote'),
            trans('vue script trans single quote :param', { param: 'test' }),
            trans("vue script trans double quote"),
            trans("vue script trans double quote :param", { param: 'test' }),
            trans(`vue script trans backtick quote`),
            trans(`vue script trans backtick quote :param`, { param: 'test' }),

            trans_choice('vue script trans_choice single quote', 2),
            trans_choice('vue script trans_choice single quote :param', 2, { param: 'test' }),
            trans_choice("vue script trans_choice double quote", 2),
            trans_choice("vue script trans_choice double quote :param", 2, { param: 'test' }),
            trans_choice(`vue script trans_choice backtick quote`, 2),
            trans_choice(`vue script trans_choice backtick quote :param`, 2, { param: 'test' }),

            __('vue script with/slash'),
            __('vue script with bracket(s)'),
        ];
    }

}
</script>
