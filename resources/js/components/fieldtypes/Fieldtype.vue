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
        }
    },

    methods: {

        update(value) {
            this.$emit('updated', value);
        }

    }

}
</script>
