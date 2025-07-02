<script setup>
import { Modal, ModalClose, Button } from '@statamic/ui';
import { ref } from 'vue';

const props = defineProps({
    children: Number,
});

const modalOpen = ref(true);
const shouldDeleteChildren = ref(false);
</script>

<template>
    <Modal :title="__('Delete Entry')" v-model:open="modalOpen">
        <p class="mb-4" v-text="__('Are you sure you want to delete this entry?')" />
        <label class="flex items-center" v-if="children">
            <input type="checkbox" class="ltr:mr-2 rtl:ml-2" v-model="shouldDeleteChildren" />
            {{ __n('Delete child entry|Delete :count child entries', children) }}
        </label>

        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <ModalClose>
                    <Button variant="ghost" :text="__('Cancel')" />
                </ModalClose>
                <Button variant="primary" :text="__('Delete')" @click="$emit('confirm', shouldDeleteChildren)" />
            </div>
        </template>
    </Modal>
</template>
