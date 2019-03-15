<template>

    <publish-field-meta
        :config="config"
        :initial-value="value"
        :initial-meta="meta"
    >
    <div slot-scope="{ meta, value, loading: loadingMeta }" :class="classes">
        <label class="flex" :class="{'bold': config.bold}">
            <template v-if="config.display">{{ config.display }}</template>
            <template v-if="!config.display">{{ config.handle | deslugify | titleize }}</template>
            <i class="required" v-if="config.required">*</i>
            <svg-icon name="translate" class="h-4 ml-sm w-4 text-grey-60" v-if="$config.get('sites').length > 1 && config.localizable" v-popover:tooltip.top="__('Localizable field')" />
        </label>

        <div
            class="help-block -mt-1"
            v-if="config.instructions"
            v-html="$options.filters.markdown(config.instructions)" />

        <loading-graphic v-if="loadingMeta" :size="16" :inline="true" />

        <slot name="fieldtype" v-if="!loadingMeta">
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
    </publish-field-meta>

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
            return `${this.config.component || this.config.type}-fieldtype`;
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
