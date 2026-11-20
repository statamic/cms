<template>
    <div v-if="value != null">
        {{ __n(':count row|:count rows', value) }}
    </div>
</template>

<script>
import IndexFieldtype from '../IndexFieldtype.vue';

export default {
    mixins: [IndexFieldtype],
};
</script>
