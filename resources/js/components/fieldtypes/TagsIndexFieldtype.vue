<template>
    <div class="text-ellipsis">{{ formatted }}</div>
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
