<script setup>
import FormLayout from './Layout.vue';
import { Button } from '@ui';
import LeftPanel from '@/pages/layout/LeftPanel.vue';
import RightPanel from '@/pages/layout/RightPanel.vue';

const props = defineProps({
    form: Object,
});
</script>

<template>
    <FormLayout>
        <template #actions>
            <Button class="ms-2" :text="__('Save')" variant="primary" />
        </template>

        <LeftPanel>
            <p>Fields</p>
        </LeftPanel>

        <div class="py-4">
            <p>TODO: Form Builder</p>
        </div>

        <RightPanel>
            <p>Settings</p>
        </RightPanel>
    </FormLayout>
</template>
