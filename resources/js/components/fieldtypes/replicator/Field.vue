<template>

    <div class="p-2 m-0" :class="classes">

        <label class="block">
            {{ display }}
            <i class="required" v-if="field.required">*</i>
            <span v-if="isReadOnly" class="text-grey-50 font-normal text-2xs mx-sm" v-text="__('Read Only')" />
        </label>

        <div
            class="help-block"
            v-if="instructions && field.instructions_position !== 'below'"
            v-html="instructions" />

        <component
            :is="fieldtypeComponent"
            :config="field"
            :meta="meta"
            :value="value"
            :handle="field.handle"
            :name-prefix="namePrefix"
            :field-path-prefix="fieldPath"
            :has-error="hasError || hasNestedError"
            :read-only="isReadOnly"
            @input="$emit('updated', $event)"
            @meta-updated="$emit('meta-updated', $event)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
            @replicator-preview-updated="$emit('replicator-preview-updated', $event)"
        />

        <div
            class="help-block mt-1"
            v-if="instructions && field.instructions_position === 'below'"
            v-html="instructions" />

        <div v-if="hasError">
            <small class="help-block text-red mt-1" v-for="(error, i) in errors" :key="i" v-text="error" />
        </div>

    </div>

</template>

<script>
export default {

    props: {
        field: {
            type: Object,
            required: true
        },
        meta: {
            type: Object,
        },
        value: {
            required: true
        },
        parentName: {
            type: String,
            required: true
        },
        setIndex: {
            type: Number,
            required: true
        },
        fieldPath: {
            type: String
        },
        readOnly: Boolean,
    },

    inject: ['storeName'],

    computed: {

        fieldtypeComponent() {
            return `${this.field.component || this.field.type}-fieldtype`;
        },

        namePrefix() {
            return `${this.parentName}[${this.setIndex}]`;
        },

        display() {
            return this.field.display || this.field.handle[0].toUpperCase() + this.field.handle.slice(1)
        },

        instructions() {
            return this.field.instructions
                ? this.$options.filters.markdown(this.field.instructions)
                : null
        },

        storeState() {
            return this.$store.state.publish[this.storeName] || [];
        },

        errors() {
            return this.storeState.errors[this.fieldPath] || [];
        },

        hasError() {
            return this.errors.length > 0;
        },

        hasNestedError() {
            const prefix = `${this.fieldPath}.`;

            return Object.keys(this.storeState.errors).some(handle => handle.startsWith(prefix));
        },

        isReadOnly() {
            return this.readOnly || this.field.visibility === 'read_only' || false;
        },

        classes() {
            return [
                'form-group publish-field',
                `${this.field.type}-fieldtype`,
                `field-${tailwind_width_class(this.field.width)}`,
                this.isReadOnly ? 'read-only-field' : '',
                this.field.classes || '',
                { 'has-error': this.hasError || this.hasNestedError }
            ];
        }

    }

}
</script>
