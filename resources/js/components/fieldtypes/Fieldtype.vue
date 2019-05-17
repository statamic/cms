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
        readOnly: {
            type: Boolean,
            default: false
        },
    },

    methods: {
        update(value) {
            this.$emit('input', value);
        }
    },

    computed: {
        isReadOnly() {
            return this.readOnly || this.config.read_only || false;
        }
    }

}
</script>
