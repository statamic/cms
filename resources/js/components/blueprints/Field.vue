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
        isEditing: Boolean,
        isSectionExpanded: Boolean
    }

}
</script>
