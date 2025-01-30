<script>
export default {

    props: {
        limit: {
            type: Number,
            required: false
        }
    },

    computed: {
        limitReached() {
            return this.currentLength > this.limit;
        },
        currentLength() {
            return (this.modelValue) ? this.modelValue.length : 0
        },
        limitIndicatorColor() {
            return this.limitReached ? 'text-red-500' : 'text-gray'
        }
    }

}
</script>
