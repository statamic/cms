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
            if (! this.isInputEvent(input) && ! this.isPointerEvent(input) && ! this.isKeyboardEvent(input)) return;

            Statamic.user.cursor = {
                handle: this.handle,
                position: {
                    start: input.target.selectionStart,
                    end: input.target.selectionStart,
                }
            }

            console.log(Statamic.user.cursor)
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
