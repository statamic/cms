<script setup>
import { Modal, ModalClose, Button } from '@/components/ui';
import { ref } from 'vue';

defineProps({
    children: Number,
});

const modalOpen = ref(true);
const shouldDeleteChildren = ref(false);
</script>

<template>
    <Modal :title="__('Delete Term')" v-model:open="modalOpen" @dismissed="$emit('cancel')">
        <p class="mb-4" v-text="__('Are you sure you want to delete this term?')" />
        <label class="flex items-center" v-if="children">
            <input type="checkbox" class="ltr:mr-2 rtl:ml-2" v-model="shouldDeleteChildren" />
            {{ __n('Delete child term|Delete :count child terms', children) }}
        </label>

        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <ModalClose>
                    <Button variant="ghost" :text="__('Cancel')" />
                </ModalClose>
                <Button
                    variant="danger"
                    :text="__('Delete')"
                    @click="$emit('confirm', shouldDeleteChildren)"
                />
            </div>
        </template>
    </Modal>
</template>
