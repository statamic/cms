<template>

    <td class="grid-cell" :class="fieldtypeComponent">
        <div v-show="showInner">
            <component
                :is="fieldtypeComponent"
                :config="field"
                :value="value"
                :meta="meta"
                :handle="field.handle"
                :name-prefix="namePrefix"
                :read-only="grid.isReadOnly"
                @input="$emit('updated', $event)"
                @meta-updated="$emit('meta-updated', $event)"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
            />
        </div>

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
        meta: {
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
        },
        showInner: {
            type: Boolean,
            required: true
        }
    },

    inject: ['storeName', 'grid'],

    computed: {

        fieldtypeComponent() {
            return `${this.field.component || this.field.type}-fieldtype`;
        },

        namePrefix() {
            return `${this.gridName}[${this.rowIndex}]`;
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

    }

}

// TODO: Cell widths
</script>
