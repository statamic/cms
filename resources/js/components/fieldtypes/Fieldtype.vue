<script>
export default {

    props: {
        initialValue: {
            required: true
        },
        config: {
            type: Object,
            required: true
        }
    },

    methods: {

        update(value) {
            this.$emit('updated', value);
        }

    }

}
</script>
