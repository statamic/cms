<template>

    <div class="p-2 m-0" :class="classes">

        <label class="block">
            {{ display }}
            <span v-if="isReadOnly" class="text-grey-50 font-normal text-2xs mx-sm" v-text="__('Read Only')" />
        </label>

        <div
            class="help-block"
            v-if="field.instructions"
            v-html="$options.filters.markdown(field.instructions)" />

        <div v-if="hasError">
            <small class="help-block text-red" v-for="(error, i) in errors" :key="i" v-text="error" />
        </div>

        <component
            :is="fieldtypeComponent"
            :config="field"
            :meta="meta"
            :value="value"
            :handle="field.handle"
            :name-prefix="namePrefix"
            :error-key-prefix="errorKey"
            :read-only="isReadOnly"
            @input="$emit('updated', $event)"
            @meta-updated="$emit('meta-updated', $event)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
            @replicator-preview-updated="$emit('replicator-preview-updated', $event)"
        />

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
        errorKey: {
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

        hasError() {
            return this.errors.length > 0;
        },

        errors() {
            const state = this.$store.state.publish[this.storeName];
            if (! state) return [];
            return state.errors[this.errorKey] || [];
        },

        isReadOnly() {
            return this.readOnly || this.field.read_only || false;
        },

        classes() {
            return [
                'form-group publish-field',
                `${this.field.type}-fieldtype`,
                `field-${tailwind_width_class(this.field.width)}`,
                this.isReadOnly ? 'read-only-field' : '',
                this.field.classes || '',
                { 'has-error': this.hasError }
            ];
        }

    }

}
</script>
