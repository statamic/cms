<script setup>
import FormLayout from './Layout.vue';
import { Button } from '@ui';

const props = defineProps({
    form: Object,
});

// TODO: Tell Vue to hide the sidebar (it should act as an overlay, like on mobile)
</script>

<template>
    <FormLayout>
        <template #actions>
            <Button class="ms-2" :text="__('Save')" variant="primary" />
        </template>

        <div class="py-4">
            <p>TODO: Form Builder</p>
        </div>
    </FormLayout>
</template>
