<script setup>
import { DialogTitle } from 'reka-ui';
import { defineOptions } from 'vue';

defineOptions({
    name: 'ModalTitle',
});
</script>

<template>
    <DialogTitle data-ui-modal-title>
        <slot />
    </DialogTitle>
</template>
