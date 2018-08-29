<template>

    <div :class="classes">
        <label class="block" :class="{'bold': config.bold, 'text-red': hasError}">
            <template v-if="config.display">{{ config.display }}</template>
            <template v-if="!config.display">{{ config.name | deslugify | titleize }}</template>
            <i class="required" v-if="config.required">*</i>
        </label>

        <small
            class="help-block"
            v-if="config.instructions"
            v-html="$options.filters.markdown(config.instructions)" />

        <div v-if="hasError">
            <small class="help-block text-red" v-for="(error, i) in errors" :key="i" v-text="error" />
        </div>

        <component
            :is="fieldtypeComponent"
            :config="config"
            :initial-value="value"
            @updated="updated"
        />
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
        errors: {
            type: Array
        }
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
                tailwind_width_class(this.config.width),
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
