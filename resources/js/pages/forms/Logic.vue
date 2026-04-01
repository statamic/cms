<script setup>
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';
import { Button } from '@ui';

defineOptions({ layout: [Layout, FormsLayout] });

const props = defineProps({
    form: Object,
});
</script>

<template>
    <Teleport to="#form-layout-actions">
        <Button :text="__('Save')" variant="primary" />
    </Teleport>

    <div class="py-4">
        <p>TODO: Logic</p>
    </div>
</template>
