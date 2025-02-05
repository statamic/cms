<template>
    <div class="flex items-center">
        <svg-icon name="light/check" class="w-3 text-green-600" v-if="this.value" />
        <svg-icon name="light/close" class="w-3 text-gray-500" v-if="!this.value" />
    </div>
</template>

<script>
import IndexFieldtype from './IndexFieldtype.vue';

export default {
    mixins: [IndexFieldtype],
};
</script>
