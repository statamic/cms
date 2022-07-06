<template>

    <td class="grid-cell" :class="classes" :width="width">
        <div v-show="showInner">
            <component
                :is="fieldtypeComponent"
                :config="field"
                :value="value"
                :meta="meta"
                :handle="field.handle"
                :name-prefix="namePrefix"
                :field-path-prefix="fieldPath"
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
        },
        errors: {
            type: Array,
            required: true
        },
        fieldPath: {
            type: String,
            required: true
        }
    },

    inject: ['grid'],

    computed: {

        classes() {
            return [this.fieldtypeComponent, this.field.classes];
        },

        fieldtypeComponent() {
            return `${this.field.component || this.field.type}-fieldtype`;
        },

        namePrefix() {
            return `${this.gridName}[${this.rowIndex}]`;
        },

        hasError() {
            return this.errors.length > 0;
        },

        width() {
            if (this.field.width) {
                return this.field.width + '%';
            }
        }

    }

}

// TODO: Cell widths
</script>
