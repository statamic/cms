<template>
    <modal name="keyboard-shortcuts" width="380" height="auto" :pivotY=".1">
        <h1 class="p-2 bg-grey-lightest border-b text-center">
            {{ __('Keyboard Shortcuts') }}
        </h1>
        <div class="p-4 relative">
            <div class="shortcut-pair">
                <span class="shortcut-key">
                    <span class="shortcut">shift</span><span class="shortcut-joiner">+</span><span class="shortcut">?</span>
                </span>
                <span class="shortcut-value">{{ __('Show Keyboard Shortcuts') }}</span>
            </div>

            <div class="shortcut-pair">
                <span class="shortcut-key">
                    <span class="shortcut">/</span> <span class="shortcut-joiner">or</span>
                    <span class="shortcut">ctrl</span><span class="shortcut-joiner">+</span><span class="shortcut">f</span>
                </span>
                <span class="shortcut-value">{{ __('Focus Search') }}</span>
            </div>

            <div class="shortcut-pair">
                <span class="shortcut-key">
                    <span class="shortcut">cmd</span><span class="shortcut-joiner">+</span><span class="shortcut">s</span>
                </span>
                <span class="shortcut-value">{{ __('Save Content') }}</span>
            </div>

            <div class="shortcut-pair">
                <span class="shortcut-key">
                    <span class="shortcut">cmd</span><span class="shortcut-joiner">+</span><span class="shortcut">\</span>
                </span>
                <span class="shortcut-value">{{ __('Toggle Sidebar') }}</span>
            </div>

            <div class="shortcut-pair mb-0">
                <span class="shortcut-key">
                    <span class="shortcut">esc</span>
                </span>
                <span class="shortcut-value">{{ __('Close Modal') }}</span>
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
