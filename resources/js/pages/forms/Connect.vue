<script setup>
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';

defineOptions({ layout: [Layout, FormsLayout] });

const props = defineProps({
    form: Object,
});
</script>

<template>
    <div class="py-4">
        <p>TODO: Connect</p>
    </div>
</template>
