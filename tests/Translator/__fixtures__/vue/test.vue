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
        ];
    }

}
</script>
