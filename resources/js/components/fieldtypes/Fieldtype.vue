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
        name: {
            type: String,
            required: true
        },
        meta: {
            type: Object,
            default: () => { return {}; }
        },
        livePreview: Boolean,
        readOnly: {
            type: Boolean,
            default: false
        }
    },

    methods: {

        update(value) {
            this.$emit('updated', value);
        }

    },

    computed: {
        isReadOnly() {
            return this.readOnly || this.config.read_only || false;
        }
    }

}
</script>
