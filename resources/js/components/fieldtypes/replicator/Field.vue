<template>
    <div class="m-0 p-4 @container" :class="classes">
        <div class="field-inner">
            <label class="block" :for="fieldId" v-if="showLabel">
                <span v-if="showLabelText" v-tooltip="{ content: field.handle, delay: 500, autoHide: false }">{{
                    display
                }}</span>
                <i class="required" v-if="field.required">*</i>
                <span v-if="isReadOnly" class="mx-1 text-2xs font-normal text-gray-500" v-text="__('Read Only')" />
            </label>

            <div
                class="help-block"
                :class="{ '-mt-2': showLabel }"
                v-if="instructions && field.instructions_position !== 'below'"
                v-html="instructions"
            />

            <publish-field-actions v-if="shouldShowFieldActions" :actions="fieldActions" />
        </div>

        <component
            :is="fieldtypeComponent"
            ref="field"
            :config="field"
            :meta="meta"
            :value="value"
            :handle="field.handle"
            :name-prefix="namePrefix"
            :field-path-prefix="fieldPath"
            :has-error="hasError || hasNestedError"
            :read-only="isReadOnly"
            :show-field-previews="showFieldPreviews"
            @update:value="$emit('updated', $event)"
            @meta-updated="$emit('meta-updated', $event)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
            @replicator-preview-updated="$emit('replicator-preview-updated', $event)"
        />

        <div
            class="help-block mt-2"
            v-if="instructions && field.instructions_position === 'below'"
            v-html="instructions"
        />

        <div v-if="hasError">
            <small class="help-block mb-0 mt-2 text-red-500" v-for="(error, i) in errors" :key="i" v-text="error" />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        field: {
            type: Object,
            required: true,
        },
        meta: {
            type: Object,
        },
        value: {
            required: true,
        },
        parentName: {
            type: String,
            required: true,
        },
        setIndex: {
            type: Number,
            required: true,
        },
        fieldPath: {
            type: String,
        },
        readOnly: Boolean,
        showFieldPreviews: Boolean,
    },

    inject: {
        store: { default: null },
        isInsideConfigFields: { default: false },
    },

    data() {
        return {
            hasField: false,
        };
    },

    computed: {
        fieldtypeComponent() {
            return `${this.field.component || this.field.type}-fieldtype`;
        },

        namePrefix() {
            return `${this.parentName}[${this.setIndex}]`;
        },

        display() {
            return __(this.field.display || this.field.handle[0].toUpperCase() + this.field.handle.slice(1));
        },

        instructions() {
            return this.field.instructions ? markdown(__(this.field.instructions)) : null;
        },

        storeState() {
            return this.store || [];
        },

        errors() {
            return this.storeState.errors[this.fieldPath] || [];
        },

        hasError() {
            return this.errors.length > 0;
        },

        hasNestedError() {
            const prefix = `${this.fieldPath}.`;

            return Object.keys(this.storeState.errors).some((handle) => handle.startsWith(prefix));
        },

        isReadOnly() {
            return this.readOnly || this.field.visibility === 'read_only' || false;
        },

        classes() {
            return [
                'form-group publish-field',
                `${this.field.type}-fieldtype`,
                `${tailwind_width_class(this.field.width)}`,
                this.showLabel ? 'has-field-label' : '',
                this.shouldShowFieldActions ? 'has-field-dropdown' : '',
                this.isReadOnly ? 'read-only-field' : '',
                this.field.classes || '',
                { 'has-error': this.hasError || this.hasNestedError },
            ];
        },

        showLabel() {
            return (
                this.showLabelText || // Need to see the label
                this.isReadOnly || // Need to see the "Read Only" text
                this.field.required
            ); // Need to see the asterisk
        },

        showLabelText() {
            return !this.field.hide_display;
        },

        fieldId() {
            let prefix = this.fieldPath ? this.fieldPath + '.' : '';
            return prefix + 'field_' + this.field.handle;
        },

        shouldShowFieldActions() {
            return !this.isInsideConfigFields && this.fieldActions.length > 0;
        },

        fieldActions() {
            return this.hasField ? this.$refs.field.fieldActions : [];
        },
    },

    mounted() {
        if (this.$refs.field) this.hasField = true;
    },
};
</script>
