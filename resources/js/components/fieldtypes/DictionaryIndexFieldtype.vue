<template>
    <div class="text-ellipsis" v-html="formatted" />
</template>

<script>
import IndexFieldtype from './IndexFieldtype.vue';
import DOMPurify from 'dompurify';

export default {
    mixins: [IndexFieldtype],

    computed: {
        formatted() {
            if (!this.value) {
                return null;
            }

            return DOMPurify.sanitize(this.value.join(', '), {
                USE_PROFILES: { html: true, svg: true },
            });
        },
    },
};
</script>
