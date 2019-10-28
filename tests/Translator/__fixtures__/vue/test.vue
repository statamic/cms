<template>

    <div>
        {{ __('vue template') }}
        {{ __n('vue template :count', 3) }}
        {{ __n('vue template :param', { param: 'test' }) }}
    </div>

</template>

<script>
export default {

    computed: {

        one() {
            return __('vue script');
        },

        two() {
            return __n('vue script :count', 2);
        },

        three() {
            return __n('vue script :param', { param: 'test' });
        }

    }

}
</script>
