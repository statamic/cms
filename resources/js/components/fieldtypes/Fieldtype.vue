<script>
import HasFieldActions from '../field-actions/HasFieldActions';
import debounce from '@statamic/util/debounce.js';
import props from './props.js';
import emits from './emits.js';

export default {
    emits,

    mixins: [HasFieldActions],

    inject: {
        fieldActionStore: {
            from: 'store',
            default: null,
        },
        fieldActionStoreName: {
            from: 'storeName',
            default: null,
        },
    },

    props,

    methods: {
        update(value) {
            this.$emit('update:value', value);
        },

        updateDebounced: debounce(function (value) {
            this.update(value);
        }, 150),

        updateMeta(value) {
            this.$emit('meta-updated', value);
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
            return (
                this.readOnly ||
                this.config.visibility === 'read_only' ||
                this.config.visibility === 'computed' ||
                false
            );
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return this.value;
        },

        fieldPathKeys() {
            const prefix = this.fieldPathPrefix || this.handle;

            return prefix.split('.');
        },

        // Deprecated, use `this.id`/`props.id` instead
        fieldId() {
            return this.id;
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
                store: this.fieldActionStore,
                storeName: this.fieldActionStoreName,
            };
        },
    },

    watch: {
        replicatorPreview: {
            immediate: true,
            handler(text) {
                if (!this.showFieldPreviews || !this.config.replicator_preview) return;

                this.$emit('replicator-preview-updated', text);
            },
        },
    },
};
</script>
