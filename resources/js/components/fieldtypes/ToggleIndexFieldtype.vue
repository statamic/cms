<template>
    <div class="flex items-center">
        <ui-icon name="checkmark" class="text-green-600" v-if="this.value" />
        <ui-icon name="x" class="text-gray-400 dark:text-gray-600" v-if="!this.value" />
    </div>
</template>

<script>
import IndexFieldtype from './IndexFieldtype.vue';

export default {
    mixins: [IndexFieldtype],
};
</script>
