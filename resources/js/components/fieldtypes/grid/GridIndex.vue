<template>

    <div v-if="modelValue">
        {{ __n(':count row|:count rows', modelValue) }}
    </div>

</template>

<script>
import IndexFieldtype from '../IndexFieldtype.vue';

export default {
    mixins: [IndexFieldtype],
}
</script>
