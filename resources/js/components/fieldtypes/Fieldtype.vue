<script>
import HasFieldActions from '../field-actions/HasFieldActions';
import debounce from '@statamic/util/debounce.js';
import props from './props.js';
import emits from './emits.js';
import { publishContextKey } from '@statamic/ui';
import { isRef } from 'vue';

export default {
    emits,

    mixins: [HasFieldActions],

    inject: {
        injectedPublishContainer: {
            from: publishContextKey
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
            this.$emit('update:meta', value);
        },
    },

    computed: {
        publishContainer() {
            // The injectedPublishContainer contains refs. We'll unwrap everything so that we can do
            // this.publishContainer.someValue instead of this.publishContainer.someValue.value
            // When using the Options API, this feels more natural. However since this is a
            // computed, it won't be avaialble within data(). In those cases you will
            // need to use this.injectedPublishContainer.someValue.value directly.
            return Object.fromEntries(
               Object.entries(this.injectedPublishContainer).map(([key, value]) => [
                   key,
                   isRef(value) ? value.value : value,
               ])
           );
        },

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
