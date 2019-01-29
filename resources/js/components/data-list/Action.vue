<script>
export default {

    props: {
        action: Object
    },

    render() {
        return this.$scopedSlots.default({
            action: this.action,
            select: this.select
        });
    },

    methods: {

        select() {
            if (this.action.confirm && !confirm('Are you sure?')) return;

            this.$emit('selected');
        }

    }

}
</script>
