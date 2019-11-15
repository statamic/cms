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
        errorKeyPrefix: String,
    },

    methods: {
        update(value) {
            this.$emit('input', value);
        },

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
            return this.readOnly || this.config.read_only || false;
        },

        replicatorPreview() {
            if (!this.value) return null;

            if (typeof this.value === 'string') return this.value;

            const json = JSON.stringify(this.value);

            if (json === '[]' || json === '{}') return null;

            return json;
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
