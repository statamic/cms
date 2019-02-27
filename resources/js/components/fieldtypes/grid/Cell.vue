<template>

    <td class="border">
        <component
            :is="fieldtypeComponent"
            :config="field"
            :value="value"
            :name="name"
            @updated="updated"
        />

        <div v-if="hasError">
            <small class="help-block text-red mt-1 mb-0" v-for="(error, i) in errors" :key="i" v-text="error" />
        </div>
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

    inject: ['storeName'],

    computed: {

        fieldtypeComponent() {
            return `${this.field.type}-fieldtype`;
        },

        name() {
            return `${this.gridName}[${this.rowIndex}][${this.field.handle}]`;
        },

        hasError() {
            return this.errors.length > 0;
        },

        errorKey() {
            return `${this.gridName}.${this.rowIndex}.${this.field.handle}`;
        },

        errors() {
            const state = this.$store.state.publish[this.storeName];
            if (! state) return [];
            return state.errors[this.errorKey] || [];
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
