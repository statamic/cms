<template>
    <div data-markdown-toolbar>
        <div class="flex items-center" v-if="!isReadOnly">
            <Button
                size="sm"
                variant="ghost"
                class="px-2! [&_svg]:size-3.5"
                v-for="button in buttons"
                :key="button.name"
                v-tooltip="button.text"
                :aria-label="button.text"
                @click="handleButtonClick(button)"
            >
                <svg-icon :name="button.svg" class="size-4" />
            </Button>
            <Button
                v-if="showDarkMode"
                size="sm"
                variant="ghost"
                class="px-2! [&_svg]:size-3.5"
                @click="$emit('toggle-dark-mode')"
                v-tooltip="darkMode ? __('Light Mode') : __('Dark Mode')"
                :aria-label="__('Toggle Dark Mode')"
            >
                <svg-icon name="dark-mode" class="size-4" />
            </Button>
        </div>
        <ToggleGroup v-model="mode" size="sm" class="-me-1" data-markdown-mode-toggle>
            <ToggleItem icon="pencil" value="write" v-tooltip="__('Writing Mode')" />
            <ToggleItem icon="eye" value="preview" v-tooltip="__('Preview Mode')" />
        </ToggleGroup>
    </div>
</template>

<script>
import { Button, ToggleGroup, ToggleItem } from '@statamic/ui';

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
