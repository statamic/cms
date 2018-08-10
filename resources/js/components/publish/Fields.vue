<template>

    <div class="publish-fields">

        <component
            v-for="(field, index) in fields"
            :is="componentName(field)"
            :field="field"
            :data.sync="data[field.name]"
            :config="fields[index]"
            :autofocus="autofocus && index == 0"
            :env="definedInEnvironment(field.name)"
        ></component>

    </div>

</template>

<script>
export default {

    components: {
        MetaField: require('./MetaField.vue'),
        RegularField: require('./RegularField.vue')
    },

    props: {
        fields: Array,
        data: Object,
        errors: Object,
        hiddenFields: {
            type: Array,
            default: []
        },
        autofocus: Boolean,
        regularTitleField: {
            type: Boolean,
            default: false
        },
        env: {
            type: Object,
            default: {}
        }
    },

    methods: {

        componentName(field) {
            return this.isMeta(field) ? 'MetaField' : 'RegularField';
        },

        widthText: function(width) {
            var width = width || 100;
            return _.findWhere(this.widths, {value: width}).text;
        },

        hasError: function(field) {
            return _.has(this.errors, 'fields.'+field.name);
        },

        isVisible(field) {
            return !this.hiddenFields.includes(field.name);
        },

        isMeta(field) {
            // Title is considered a meta tag by default. If a component (eg. asset editor)
            // wants to consider title just a regular ol' field, they can pass in this prop.
            if (field.name === 'title' && this.regularTitleField) {
                return;
            }

            return field.isMeta || ['title', 'slug', 'date'].includes(field.name);
        },

        definedInEnvironment: function(name) {
            return _.has(this.env, name);
        }
    },

};
</script>
