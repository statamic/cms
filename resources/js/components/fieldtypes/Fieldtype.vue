<script>
import HasFieldActions from '../field-actions/HasFieldActions';

export default {
    emits: ['input', 'focus', 'blur', 'meta-updated', 'replicator-preview-updated'],

    mixins: [
        HasFieldActions,
    ],

    inject: {
        fieldActionStoreName: {
            from: 'storeName',
            default: null,
        }
    },

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
        showFieldPreviews: {
            type: Boolean,
            default: false
        },
        namePrefix: String,
        fieldPathPrefix: String,
    },

    methods: {
        update(value) {
            this.$emit('input', value);
        },

        updateDebounced: _.debounce(function (value) {
            this.update(value);
        }, 150),

        updateMeta(value) {
            this.$emit('meta-updated', value);
        }
    },

    computed: {
        name() {
            if (this.namePrefix) {
                return `${this.namePrefix}[${this.handle}]`;
            }

            return this.handle;
        },

        isReadOnly() {
            return this.readOnly
                || this.config.visibility === 'read_only'
                || this.config.visibility === 'computed'
                || false;
        },

        replicatorPreview() {
            if (! this.showFieldPreviews || ! this.config.replicator_preview) return;

            return this.value;
        },

        fieldPathKeys() {
            const prefix = this.fieldPathPrefix || this.handle;

            return prefix.split('.');
        },

        fieldId() {
            let prefix = this.fieldPathPrefix ? this.fieldPathPrefix+'.' : '';

            return prefix+'field_'+this.config.handle;
        },

        fieldActionPayload() {
            return {
                vm: this,
                fieldPathPrefix: this.fieldPathPrefix,
                handle: this.handle,
                value: this.value,
                config: this.config,
                meta: this.meta,
                update: this.update,
                updateMeta: this.updateMeta,
                isReadOnly: this.isReadOnly,
                store: this.$store,
                storeName: this.fieldActionStoreName,
            };
        },

    },

    watch: {

        replicatorPreview: {
            immediate: true,
            handler(text) {
                if (! this.showFieldPreviews || ! this.config.replicator_preview) return;

                this.$emit('replicator-preview-updated', text);
            }
        }

    }

}
</script>
