<template>

    <div class="form-group">
        <label class="block" :class="{'bold': config.bold}">
            <template v-if="config.display">{{ config.display }}</template>
            <template v-if="!config.display">{{ config.name | deslugify | titleize }}</template>
            <i class="required" v-if="config.required">*</i>
        </label>

        <small
            class="help-block"
            v-if="config.instructions"
            v-html="$options.filters.markdown(config.instructions)" />

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
        }
    },

    computed: {

        fieldtypeComponent() {
            return `${this.config.type}-fieldtype`;
        }

    },

    methods: {

        updated(value) {
            this.$emit('updated', this.config.handle, value);
        }

    }

}
</script>
