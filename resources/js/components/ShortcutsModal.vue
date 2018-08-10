<template>
    <modal-dialog :show="show" @close="dismiss">
        <h1 slot="header">{{ translate('cp.keyboard_shortcuts') }}</h1>
        <slot>
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
                <span class="shortcut-value">{{ translate('cp.search') }}</span>
            </div>

            <div class="shortcut-pair">
                <span class="shortcut-key">
                    <span class="shortcut">cmd</span><span class="shortcut-joiner">+</span><span class="shortcut">s</span>
                </span>
                <span class="shortcut-value">{{ translate('cp.publish_content') }}</span>
            </div>

            <div class="shortcut-pair">
                <span class="shortcut-key">
                    <span class="shortcut">Esc</span>
                </span>
                <span class="shortcut-value">{{ translate('cp.close_this_window') }}</span>
            </div>

        </slot>

        <template slot="footer"></template>
    </modal-dialog>
</template>

<script>
import ModalDialog from "./ModalDialog.vue"

export default {
    components: {
        ModalDialog
    },
    props: ["show"],
    methods: {
        dismiss() {
            this.$emit("close")
        }
    }
}
</script>
