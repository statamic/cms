<template>
    <div
        class="flex items-center justify-between rounded-t-xl border-b border-gray-300 bg-gray-50 px-2 py-1 dark:border-white/15 dark:bg-gray-950"
    >
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
        <div class="flex items-center">
            <Button
                size="sm"
                variant="ghost"
                class="px-2! [&_svg]:size-3.5"
                @click="$emit('toggle-mode', 'write')"
                :class="mode === 'write' ? 'text-gray-900! dark:text-white!' : 'text-gray-400!'"
                v-text="__('Write')"
                :aria-pressed="mode === 'write' ? 'true' : 'false'"
            />
            <Button
                size="sm"
                variant="ghost"
                class="px-2! [&_svg]:size-3.5"
                @click="$emit('toggle-mode', 'preview')"
                :class="mode === 'preview' ? 'text-gray-900! dark:text-white!' : 'text-gray-400!'"
                v-text="__('Preview')"
                :aria-pressed="mode === 'preview' ? 'true' : 'false'"
            />
        </div>
    </div>
</template>

<script>
import { Button } from '@statamic/ui';

export default {
    components: {
        Button,
    },
    props: {
        mode: {
            type: String,
            required: true,
        },
        buttons: {
            type: Array,
            required: true,
        },
        isReadOnly: {
            type: Boolean,
            default: false,
        },
        showDarkMode: {
            type: Boolean,
            default: false,
        },
        darkMode: {
            type: Boolean,
            default: false,
        },
        isFullscreen: {
            type: Boolean,
            default: false,
        },
    },

    emits: ['toggle-mode', 'toggle-dark-mode', 'button-click'],

    methods: {
        handleButtonClick(button) {
            this.$emit('button-click', button.command);
        },
    },
};
</script>
