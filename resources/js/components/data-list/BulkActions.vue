<script>
export default {
    inject: ['sharedState'],

    render() {
        return this.$scopedSlots.default({
            selections: this.sharedState.selections,
            hasSelections: this.sharedState.selections.length > 0,
        });
    }
}
</script>
