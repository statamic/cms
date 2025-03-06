<template>
    <modal v-if="open" name="keyboard-shortcuts" :width="380" @closed="open = false" click-to-close>
        <div class="-max-h-screen-px">
            <h1 class="border-b bg-gray-200 p-4 text-center dark:border-dark-900 dark:bg-dark-700">
                {{ __('Keyboard Shortcuts') }}
            </h1>
            <div class="relative p-6">
                <div class="shortcut-pair">
                    <span class="shortcut-combo">
                        <span class="shortcut">shift</span><span class="shortcut-joiner">+</span
                        ><span class="shortcut">?</span>
                    </span>
                    <span class="shortcut-value">{{ __('Show Keyboard Shortcuts') }}</span>
                </div>

                <div class="shortcut-pair">
                    <span class="shortcut-combo">
                        <span class="shortcut">/</span> <span class="shortcut-joiner">or</span>
                        <span class="shortcut">ctrl</span><span class="shortcut-joiner">+</span
                        ><span class="shortcut">f</span>
                    </span>
                    <span class="shortcut-value">{{ __('Focus Search') }}</span>
                </div>

                <div class="shortcut-pair">
                    <span class="shortcut-combo">
                        <span class="shortcut">⌘</span><span class="shortcut-joiner">+</span
                        ><span class="shortcut">return</span>
                    </span>
                    <span class="shortcut-value">{{ __('Save') }}</span>
                </div>

                <div class="shortcut-pair">
                    <span class="shortcut-combo">
                        <span class="shortcut">⌘</span><span class="shortcut-joiner">+</span
                        ><span class="shortcut">s</span>
                    </span>
                    <span class="shortcut-value">{{ __('Quick Save') }}</span>
                </div>

                <div class="shortcut-pair">
                    <span class="shortcut-combo">
                        <span class="shortcut">⌘</span><span class="shortcut-joiner">+</span
                        ><span class="shortcut">\</span>
                    </span>
                    <span class="shortcut-value">{{ __('Toggle Sidebar') }}</span>
                </div>

                <div class="shortcut-pair mb-0">
                    <span class="shortcut-combo">
                        <span class="shortcut">esc</span>
                    </span>
                    <span class="shortcut-value">{{ __('Close Modal') }}</span>
                </div>
            </div>
        </div>
    </modal>
</template>

<script>
export default {
    data() {
        return {
            open: false,
            keybinding: null,
        };
    },

    watch: {
        open(open) {
            if (open) {
                this.keybinding = this.$keys.bind('esc', () => (this.open = false));
            } else {
                this.keybinding.destroy();
            }
        },
    },

    methods: {
        close() {
            this.open = false;
        },
    },

    created() {
        this.$keys.bind('?', () => (this.open = !this.open));

        this.$events.$on('keyboard-shortcuts.open', () => {
            this.open = true;
        });
    },
};
</script>
