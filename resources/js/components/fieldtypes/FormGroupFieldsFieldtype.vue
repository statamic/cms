<script>
import FieldsFieldtype from './grid/FieldsFieldtype.vue';
import { categories } from '@/components/forms/builder/categories';
import { builderContextKey } from '@/components/forms/builder/context';

const unsupportedChildFieldtypes = ['group', 'upload', 'form_upload', 'assets', 'files'];

export default {
    extends: FieldsFieldtype,

    inject: {
        formBuilder: { from: builderContextKey, default: null },
    },

    provide() {
        if (!this.formBuilder) return {};

        return {
            fieldtypeSelectorSource: {
                fieldtypes: this.formBuilder.fieldtypes.filter((fieldtype) => !unsupportedChildFieldtypes.includes(fieldtype.handle)),
                categories: Object.fromEntries(Object.entries(categories).map(([handle, category]) => [handle, { title: category.title }])),
                defaultCategory: 'other',
                createField: this.createChildField,
            },
            fieldSettingsApi: {
                canSaveRoot: false,
                edit: (payload) => this.$axios.post(this.builderFieldsUrl('edit'), payload).then((response) => response.data),
                update: (payload) => this.$axios.post(this.builderFieldsUrl('update'), payload).then((response) => response.data.values),
            },
        };
    },

    methods: {
        builderFieldsUrl(action) {
            return cp_url(`forms/${this.formBuilder.form.handle}/builder/fields/${action}`);
        },

        createChildField(fieldtype) {
            const defaults = Object.fromEntries(fieldtype.config.map((configField) => [configField.handle, configField.default ?? null]));

            return {
                icon: fieldtype.icon,
                config: {
                    ...defaults,
                    type: fieldtype.handle,
                    display: __(fieldtype.title),
                    handle: null,
                    instructions: null,
                    width: 100,
                    isNew: true,
                },
            };
        },
    },
};
</script>
