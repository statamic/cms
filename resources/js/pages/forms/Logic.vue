<script setup>
import FormLayout from './Layout.vue';
import { Button } from '@ui';

const props = defineProps({
    form: Object,
});
</script>

<template>
    <FormLayout>
        <template #actions>
            <Button class="ms-2" :text="__('Save')" variant="primary" />
        </template>

        <div class="py-4">
            <p>TODO: Logic</p>
        </div>
    </FormLayout>
</template>
