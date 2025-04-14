<template>
    <div class="flex items-center space-x-2">
        <template v-else-if="value.setup">
            <svg-icon name="light/check" class="w-3 text-green-600" />
            <div>{{ __('Set up') }}</div>
        </template>

        <template v-else>
            <svg-icon name="light/close" class="w-3 text-gray-500" />
            <div>{{ __('Not set up') }}</div>
        </template>
    </div>
</template>

<script>
import IndexFieldtype from './IndexFieldtype.vue';

export default {
    mixins: [IndexFieldtype],
};
</script>
