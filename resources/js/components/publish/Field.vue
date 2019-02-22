<template>

    <div :class="classes">
        <label class="block" :class="{'bold': config.bold}">
            <template v-if="config.display">{{ config.display }}</template>
            <template v-if="!config.display">{{ config.handle | deslugify | titleize }}</template>
            <i class="required" v-if="config.required">*</i>
            <i class="icon icon-chat text-xs text-grey-lighten-2" v-if="config.localizable" v-popover:tooltip.top="__('This field will be localized.')" />
        </label>

        <div
            class="help-block -mt-1"
            v-if="config.instructions"
            v-html="$options.filters.markdown(config.instructions)" />

        <slot name="fieldtype">
            <component
                :is="fieldtypeComponent"
                :config="config"
                :value="value"
                :meta="meta"
                :name="config.handle"
                :live-preview="livePreview"
                @updated="updated"
            /> <!-- TODO: name prop should include prefixing when used recursively like inside a grid. -->
        </slot>

        <div v-if="hasError">
            <small class="help-block text-red mt-1 mb-0" v-for="(error, i) in errors" :key="i" v-text="error" />
        </div>
    </div>

</template>

<script>
export default {

    props: {
        config: {
            type: Object,
            required: true
        },
        value: {
            required: true
        },
        meta: {
        },
        errors: {
            type: Array
        },
        livePreview: Boolean
    },

    computed: {

        fieldtypeComponent() {
            return `${this.config.type}-fieldtype`;
        },

        hasError() {
            return this.errors && this.errors.length > 0;
        },

        classes() {
            return [
                'form-group',
                `${this.config.type}-fieldtype`,
                !this.livePreview ? tailwind_width_class(this.config.width) : '',
                this.config.classes || '',
                { 'has-error': this.hasError }
            ];
        }

    },

    methods: {

        updated(value) {
            this.$emit('updated', this.config.handle, value);
        }

    }

}
</script>
