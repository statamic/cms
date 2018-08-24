<script>
export default {
    inject: ['sharedState'],
    computed: {
        hasCheckedIds() {
            return this.sharedState.checkedIds.length > 0
        }
    },
    render() {
        return this.$scopedSlots.default({
            ids: this.sharedState.checkedIds,
            hasCheckedIds: this.hasCheckedIds
        });
    }
}
</script>
