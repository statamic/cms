const defaultLocale = Object.keys(Statamic.locales)[0];
const currentLocale = Statamic.Publish ? (Statamic.Publish.locale || defaultLocale) : defaultLocale;
const isEditingDefaultLocale = currentLocale === defaultLocale;

export default {

    props: ['field', 'data', 'config', 'autofocus', 'env'],

    computed: {

        isVisible() {
            return !this.$parent.hiddenFields.includes(this.field.name);
        },

        isReadOnly() {
            return !isEditingDefaultLocale && !this.isLocalizable;
        },

        hasError() {
            return _.has(this.$parent.errors, 'fields.'+this.field.name);
        },

        classes() {
            return [
                'form-group',
                this.fieldtypeClass,
                tailwind_width_class(this.field.width),
                this.config.classes || '',
                { 'has-error': this.hasError }
            ];
        },

        fieldtypeClass() {
            return this.field.type + '-fieldtype';
        }

    },

    watch: {

        isVisible(visible) {
            // When showing fields, dispatch a resize event. Fields like Grid may be
            // listening for it to know whether they should be in stacked/table layout.
            if (visible) window.dispatchEvent(new Event('resize'));
        }

    }

}
