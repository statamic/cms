<script>
export default {

    props: {
        value: {
            required: true
        },
        config: {
            type: Object,
            required: true
        },
        name: {
            type: String,
            required: true
        },
        meta: {}
    },

    methods: {

        update(value) {
            this.$emit('updated', value);
        }

    }

}
</script>
