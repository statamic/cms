<script>
export default {

    props: {
        variables: Object
    },

    provide() {
        return this.variables;
    },

    render() {
        return this.$slots.default({})[0];
    }

}
</script>
