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
            return (this.value) ? this.value.length : 0
        },
        limitIndicatorColor() {
            return this.limitReached ? 'text-red' : 'text-grey'
        }
    }

}
</script>
