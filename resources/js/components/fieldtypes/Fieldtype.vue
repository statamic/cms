<script>
import HasFieldActions from '../field-actions/HasFieldActions';
import debounce from '@/util/debounce.js';
import props from './props.js';
import emits from './emits.js';
import { UPDATE_DEBOUNCE_MS } from './constants';
import { publishContextKey } from '@/components/ui';
import { isRef, markRaw } from 'vue';

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

        updateMeta(value) {
            this.$emit('update:meta', value);
        },
    },

    created() {
        this.updateDebounced = markRaw(debounce((value) => {
            this.update(value);
        }, UPDATE_DEBOUNCE_MS));
    },

    computed: {
        publishContainer() {
            // The injectedPublishContainer contains refs. We'll unwrap everything so that we can do
            // this.publishContainer.someValue instead of this.publishContainer.someValue.value
            // When using the Options API, this feels more natural. However since this is a
            // computed, it won't be avaialble within data(). In those cases you will
            // need to use this.injectedPublishContainer.someValue.value directly.
            //
            // We build the cache once with lazy getters, so this computed has zero reactive
            // deps and is never invalidated/recomputed itself — reading a specific key still
            // goes through to the live ref, so dependency tracking happens correctly in
            // whichever consumer's reactive scope reads it (e.g. a field's own condition
            // computed), rather than recomputing this whole object for every field on the
            // page whenever any one of the injected refs changes.
            if (this._publishContainerCache) return this._publishContainerCache;

            const cache = {};
            const src = this.injectedPublishContainer;

            for (const key in src) {
                const val = src[key];

                if (isRef(val)) {
                    Object.defineProperty(cache, key, {
                        enumerable: true,
                        configurable: true,
                        get: () => val.value,
                    });
                } else {
                    Object.defineProperty(cache, key, {
                        enumerable: true,
                        configurable: true,
                        writable: false,
                        value: val,
                    });
                }
            }

            this._publishContainerCache = cache;
            return cache;
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
            if (!this.showFieldPreviews) return;

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
                if (!this.showFieldPreviews) return;

                this.$emit('replicator-preview-updated', text);
            },
        },
    },
};
</script>
