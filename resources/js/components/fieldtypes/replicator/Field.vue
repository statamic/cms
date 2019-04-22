<template>

    <div class="p-2 m-0" :class="classes">

        <label class="block">{{ display }}</label>

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
            :value="value"
            :name="name"
            @updated="$emit('updated', $event)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
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
        }
    },

    inject: ['storeName'],

    computed: {

        fieldtypeComponent() {
            return `${this.field.type}-fieldtype`;
        },

        name() {
            return `${this.parentName}[${this.setIndex}][${this.field.handle}]`;
        },

        display() {
            return this.field.display || this.field.handle[0].toUpperCase() + this.field.handle.slice(1)
        },

        hasError() {
            return this.errors.length > 0;
        },

        errorKey() {
            return `${this.parentName}.${this.setIndex}.${this.field.handle}`;
        },

        errors() {
            const state = this.$store.state.publish[this.storeName];
            if (! state) return [];
            return state.errors[this.errorKey] || [];
        },

        isReadOnly() {
            return this.field.read_only || false;
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
