<template>

    <div :class="classes">
        <div class="field-inner">
            <div v-if="isReadOnly" class="read-only-overlay" :title="translate('cp.read_only')"></div>

            <label class="block">
                {{ display }}
                <i class="required" v-if="isRequired">*</i>
            </label>
            <small class="help-block" v-if="instructions" v-html="instructions | markdown"></small>

            <component
                v-ref="field"
                :is="componentName"
                :config="config"
                :autofocus="autofocus"
            ></component>

        </div>
    </div>

</template>

<script>
import Field from './Field';
import SlugField from './MetaFields/Slug.vue';
import DateField from './MetaFields/Date.vue';
import TitleField from './MetaFields/Title.vue';
import TaxonomyField from './MetaFields/Taxonomy.vue';

export default {

    mixins: [Field],

    components: {
        DateField,
        SlugField,
        TitleField,
        TaxonomyField
    },

    computed: {

        componentName() {
            let name = (this.field.type === 'taxonomy') ? 'taxonomy' : this.field.name;
            return `${name[0].toUpperCase()}${name.slice(1)}Field`;
        },

        display() {
            return this.$refs.field.display
                || this.config.display
                || this.$refs.field.displayFallback
                || this.field.name;
        },

        instructions() {
            return this.$refs.field.instructions || this.config.instructions;
        },

        fieldtypeClass() {
            return this.field.name + '-meta-fieldtype';
        },

        isRequired() {
            const forced = this.$refs.field.isRequired;
            if (forced !== undefined) return forced;
            return this.field.required;
        },

        isLocalizable() {
            const forced = this.$refs.field.isLocalizable;
            if (forced !== undefined) return forced;
            return this.config.localizable;
        }

    }

}
</script>
