<script>
export default {

    props: {
        value: {
            required: true
        },
        config: {
            type: Object,
            default: () => { return {}; }
        },
        handle: {
            type: String,
            required: true
        },
        meta: {
            type: Object,
            default: () => { return {}; }
        },
        readOnly: {
            type: Boolean,
            default: false
        },
        namePrefix: String,
        fieldPathPrefix: String,
    },

    methods: {

        update(input) {
            this.$emit("input", this.isInputEvent(input) ? input.target.value : input);
            this.updateCursorPosition(input);
        },

        updateDebounced: _.debounce(function (input) {
            this.update(input);
        }, 150),

        updateMeta(value) {
            this.$emit('meta-updated', value);
        },

        blurEvent() {
            Statamic.user.cursor = null;
            this.$emit('blur');
        },

        updateCursorPosition(input) {
            let position = null;

            if (this.isInputEvent(input) || this.isPointerEvent(input)) {
                position = {
                    start: input.target.selectionStart,
                    end: input.target.selectionStart
                };
            }

            if (this.isKeyboardEvent(input)) {
                // If using the keydown event, the selection position is so to say always one number behind.
                let start = input.target.selectionStart;

                // Substract one position if moving to the left, if not already on position zero.
                if (input.code === "ArrowLeft" && start !== 0) {
                    start--;
                }

                // Add one position if moving to the right, if not already on the last position.
                if (input.code === "ArrowRight" && input.target.value.length > start) {
                    start++;
                }

                // Move position to start if pressing arrow up.
                if (input.code === "ArrowUp") {
                    start = 0;
                }

                // Move position to last position if pressing arrow up.
                if (input.code === "ArrowDown") {
                    start = input.target.value.length;
                }

                position = {
                  start: start,
                  end: start // Is the same as start
                };
            }

            if (! position) return;

            Statamic.user.cursor = {
                handle: this.handle,
                position: position,
            }
        },

        isInputEvent(input) {
            return typeof input === "object" && input.constructor.name === "InputEvent";
        },

        isPointerEvent(input) {
            return typeof input === "object" && input.constructor.name === "PointerEvent";
        },

        isKeyboardEvent(input) {
            return typeof input === "object" && input.constructor.name === "KeyboardEvent";
        },

    },

    computed: {

        name() {
            if (this.namePrefix) {
                return `${this.namePrefix}[${this.handle}]`;
            }

            return this.handle;
        },

        isReadOnly() {
            return this.readOnly || this.config.read_only || false;
        },

        replicatorPreview() {
            return this.value;
        },

        fieldId() {
            return 'field_'+this.config.handle;
        }

    },

    watch: {

        replicatorPreview: {
            immediate: true,
            handler(text) {
                this.$emit('replicator-preview-updated', text);
            }
        }

    }

}
</script>
