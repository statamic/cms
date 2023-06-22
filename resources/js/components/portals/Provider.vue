<script>
export default {

    props: {
        variables: Object
    },

    provide() {
        return this.variables;
    },

    render() {
        return this.$scopedSlots.default({});
    }

}
</script>
