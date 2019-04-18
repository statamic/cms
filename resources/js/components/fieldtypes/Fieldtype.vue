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
            this.$emit('updated', value);
        }
    },

    computed: {
        isReadOnly() {
            return this.readOnly || this.config.read_only || false;
        }
    },

    mounted() {
        // todo: check if a focus function exists and use that

        let inputs = this.$el.querySelectorAll('input, textarea, select');
        if (!inputs.length) return;
        let input = inputs[0];
        input.addEventListener('focus', e => this.$emit('focus'));
        input.addEventListener('blur', e => this.$emit('blur'));
    }

}
</script>
