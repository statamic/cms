<template>
    <div class="text-ellipsis" v-html="formatted" />
</template>

<script>
import IndexFieldtype from './IndexFieldtype.vue';

export default {

    mixins: [IndexFieldtype],

    computed: {
        formatted() {
            if (!this.value) {
                return null;
            }
            return this.value.join(', ');
        },
    },
};
</script>
