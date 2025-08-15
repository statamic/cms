<script setup>
import { DialogClose } from 'reka-ui';
import { computed, useSlots } from 'vue';
import { Button } from '@/components/ui';

const slots = useSlots();

const hasSlot = computed(() => !!slots.default);
</script>

<template>
    <DialogClose data-ui-modal-close :asChild="!hasSlot">
        <template v-if="hasSlot">
            <slot />
        </template>
        <template v-else>
            <Button variant="ghost" size="sm" icon="x" class="absolute top-3 right-2" />
        </template>
    </DialogClose>
</template>
