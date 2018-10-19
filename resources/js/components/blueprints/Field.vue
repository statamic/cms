<script>
export default {

    props: {
        field: {
            type: Object,
            required: true
        },
        isRoot: {
            type: Boolean,
            default: true
        },
        isEditing: Boolean
    },

    watch: {

        isEditing(isEditing) {
            if (isEditing) this.$nextTick(() => this.$modal.show('field-settings'))
        }

    }

}
</script>
