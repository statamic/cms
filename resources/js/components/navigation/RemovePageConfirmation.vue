<script setup>
import { Modal, Button } from '@statamic/ui';
import { ref } from 'vue';

const emits = defineEmits(['confirm', 'cancel']);

const props = defineProps({
    children: Number,
});

const modalOpen = ref(true);
const shouldDeleteChildren = ref(false);
</script>

<template>
    <Modal :title="__('Remove Page')" v-model:open="modalOpen">
        <p class="mb-4" v-text="__('Are you sure you want to remove this page?')" />
        <p class="mb-4" v-text="__('Only the references will be removed. Entries will not be deleted.')" />
        <label class="flex items-center" v-if="children">
            <input type="checkbox" class="ltr:mr-2 rtl:ml-2" v-model="shouldDeleteChildren" />
            {{ __n('Remove child page|Remove :count child pages', children) }}
        </label>

        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <Button
                    variant="ghost"
                    @click="$emit('cancel')"
                    :text="__('Cancel')"
                />
                <Button
                    variant="danger"
                    @click="$emit('confirm', shouldDeleteChildren)"
                    :text="__('Remove')"
                />
            </div>
        </template>
    </Modal>
</template>
