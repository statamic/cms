<template>
    <modal name="keyboard-shortcuts" width="380" height="auto">
        <h1 class="p-2 bg-grey-lightest border-b text-center">
            {{ translate('cp.keyboard_shortcuts') }}
        </h1>
        <div class="p-4 relative">
            <div class="shortcut-pair">
                <span class="shortcut-key">
                    <span class="shortcut">shift</span><span class="shortcut-joiner">+</span><span class="shortcut">?</span>
                </span>
                <span class="shortcut-value">{{ translate('cp.show_keyboard_shortcuts') }}</span>
            </div>

            <div class="shortcut-pair">
                <span class="shortcut-key">
                    <span class="shortcut">/</span> <span class="shortcut-joiner">or</span>
                    <span class="shortcut">ctrl</span><span class="shortcut-joiner">+</span><span class="shortcut">f</span>
                </span>
                <span class="shortcut-value">{{ translate('cp.focus_search') }}</span>
            </div>

            <div class="shortcut-pair">
                <span class="shortcut-key">
                    <span class="shortcut">cmd</span><span class="shortcut-joiner">+</span><span class="shortcut">s</span>
                </span>
                <span class="shortcut-value">{{ translate('cp.save_content') }}</span>
            </div>

            <div class="shortcut-pair">
                <span class="shortcut-key">
                    <span class="shortcut">cmd</span><span class="shortcut-joiner">+</span><span class="shortcut">\</span>
                </span>
                <span class="shortcut-value">{{ translate('cp.toggle_sidebar') }}</span>
            </div>

            <div class="shortcut-pair mb-0">
                <span class="shortcut-key">
                    <span class="shortcut">Esc</span>
                </span>
                <span class="shortcut-value">{{ translate('cp.close_modal') }}</span>
            </div>
        </div>
    </modal>
</template>

<script>
export default {
    mounted() {
        this.$mousetrap.bind('?', function(e) {
            this.$modal.show('keyboard-shortcuts')
        }.bind(this), 'keyup');
    }
}
</script>
