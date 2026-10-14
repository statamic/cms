<script setup>
import { inject } from 'vue';

defineOptions({
    name: 'ModalTitle',
});

const titleId = inject('modalTitleId', null);
</script>

<template>
    <div :id="titleId" data-ui-modal-title>
        <slot />
    </div>
</template>
