<template>
    <div
        class="flex items-center justify-between bg-gray-50 dark:bg-gray-950 rounded-t-xl py-1 px-2 border-b border-gray-300 dark:border-white/15"
    >
        <div class="flex items-center" v-if="!isReadOnly">
            <ui-button
                size="sm"
                variant="ghost"
                class="px-2!"
                v-for="button in buttons"
                :key="button.name"
                v-tooltip="button.text"
                :aria-label="button.text"
                @click="handleButtonClick(button)"
            >
                <svg-icon :name="button.svg" class="size-4" />
            </ui-button>
            <ui-button
                v-if="showDarkMode"
                size="sm"
                variant="ghost"
                class="px-2!"
                @click="$emit('toggle-dark-mode')"
                v-tooltip="darkMode ? __('Light Mode') : __('Dark Mode')"
                :aria-label="__('Toggle Dark Mode')"
            >
                <svg-icon name="dark-mode" class="size-4" />
            </ui-button>
        </div>
        <div class="flex items-center">
            <ui-button
                size="sm"
                variant="ghost"
                class="px-2!"
                @click="$emit('toggle-mode', 'write')"
                :class="{ 'text-black! dark:text-white!': mode == 'write' }"
                v-text="__('Write')"
                :aria-pressed="mode === 'write' ? 'true' : 'false'"
            />
            <ui-button
                size="sm"
                variant="ghost"
                class="px-2!"
                @click="$emit('toggle-mode', 'preview')"
                :class="{ 'text-black! dark:text-white!': mode == 'preview' }"
                v-text="__('Preview')"
                :aria-pressed="mode === 'preview' ? 'true' : 'false'"
            />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        mode: {
            type: String,
            required: true
        },
        buttons: {
            type: Array,
            required: true
        },
        isReadOnly: {
            type: Boolean,
            default: false
        },
        showDarkMode: {
            type: Boolean,
            default: false
        },
        darkMode: {
            type: Boolean,
            default: false
        },
        isFullscreen: {
            type: Boolean,
            default: false
        }
    },

    emits: ['toggle-mode', 'toggle-dark-mode', 'button-click'],

    methods: {
        handleButtonClick(button) {
            this.$emit('button-click', button.command);
        }
    }
};
</script>
