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
        fields: {
            type: Array,
        },
        isEditing: Boolean
    }

}
</script>
