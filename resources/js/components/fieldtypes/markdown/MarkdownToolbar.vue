<template>
    <div data-markdown-toolbar>
        <div class="flex items-center" v-if="!isReadOnly">
            <Button
                :aria-label="button.text"
                :icon="button.svg"
                :key="button.name"
                @click="handleButtonClick(button)"
                size="sm"
                v-for="button in buttons"
                v-tooltip="button.text"
                variant="ghost"
            />
        </div>
        <ToggleGroup v-model="mode" size="sm" class="-me-1" data-markdown-mode-toggle>
            <ToggleItem icon="pencil" value="write" v-tooltip="__('Writing Mode')" />
            <ToggleItem icon="eye" value="preview" v-tooltip="__('Preview Mode')" />
        </ToggleGroup>
    </div>
</template>

<script>
import { Button, ToggleGroup, ToggleItem } from '@/components/ui';

export default {
    components: {
        Button,
        ToggleGroup,
        ToggleItem,
    },

    props: {
        buttons: { type: Array, required: true },
        isReadOnly: { type: Boolean, default: false },
        showDarkMode: { type: Boolean, default: false },
        darkMode: { type: Boolean, default: false },
        isFullscreen: { type: Boolean, default: false },
    },

    data() {
        return {
            mode: 'write',
        };
    },

    watch: {
        mode(newVal) {
            this.$emit('update:mode', newVal);
        },
    },

    emits: ['update:mode', 'toggle-dark-mode', 'button-click'],

    methods: {
        handleButtonClick(button) {
            this.$emit('button-click', button.command);
        },
    },
};
</script>
