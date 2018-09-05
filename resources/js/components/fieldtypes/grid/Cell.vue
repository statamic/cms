<template>

    <td class="border">
        <component
            :is="fieldtypeComponent"
            :config="field"
            :value="value"
            :name="name"
            @updated="updated"
        />
    </td>

</template>

<script>
export default {

    props: {
        field: {
            type: Object,
            required: true
        },
        value: {
            required: true
        },
        index: {
            type: Number,
            required: true
        },
        rowIndex: {
            type: Number,
            required: true
        },
        gridName: {
            type: String,
            required: true
        }
    },

    computed: {

        fieldtypeComponent() {
            return `${this.field.type}-fieldtype`;
        },

        name() {
            return `${this.gridName}[${this.rowIndex}][${this.field.handle}]`;
        }

    },

    methods: {

        updated(value) {
            this.$emit('updated', this.field.handle, value);
        }

    }

}

// TODO: Cell widths
</script>
