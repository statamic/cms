<script setup>
import { Badge, Button, Modal, ModalClose } from '@statamic/ui';
import { ref } from 'vue';

const activeCount = ref(2);
</script>

<template>
    <div class="flex flex-1 items-center gap-3 overflow-x-auto py-3">
        <Modal :title="__('Apply Filters')">
            <template #trigger>
                <Button icon="filter" class="relative">
                    {{ __('Filter') }}
                    <Badge
                        v-if="activeCount"
                        :text="activeCount"
                        pill
                        variant="filled"
                        class="absolute -top-1.5 -right-1.5"
                    />
                </Button>
            </template>
            <div class="space-y-6 py-3">FILTERS GO HERE</div>
            <template #footer>
                <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                    <ModalClose>
                        <Button text="Cancel" variant="ghost" />
                    </ModalClose>
                    <Button text="Update Filter" variant="primary" />
                </div>
            </template>
        </Modal>
    </div>
</template>
