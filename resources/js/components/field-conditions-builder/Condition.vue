<template>

    <tr>
        <td v-if="!isFirst" class="pl-0" width="30">
            <select-fieldtype
                :data.sync="operator"
                :config="operatorSelectConfig">
            </select-fieldtype>
        </td>
        <td :colspan="isFirst ? 2 : null" class="pl-0" width="40%">
            <input type="text" v-model="handle" placeholder="Field" v-el:handle />
        </td>
        <td>
            <select multiple v-el:values></select>
        </td>
        <td class="row-controls text-center" width="32px">
                <a class="icon icon-cross delete mt-8" @click="$emit('deleted')"></a>
        </td>
    </tr>

</template>

<script>

export default {

    props: ['index', 'handle', 'operator', 'values'],

    data() {
        return {
            operatorSelectConfig: {
                options: [
                    { text: 'and', value: 'and' },
                    { text: 'or', value: 'or' }
                ]
            }
        }
    },

    computed: {

        isFirst() {
            return this.index === 0;
        }

    },

    ready() {
        this.selectizeHandles();
        this.selectizeValues();
    },

    methods: {

        selectizeValues() {
            let options = _.map(this.values, (val) => {
                return { value: val, text: val };
            });

            $(this.$els.values).selectize({
                items: this.values,
                options: options,
                create: true,
                plugins: ['remove_button'],
                onChange: (values) => {
                    this.values = values;
                }
            });
        },

        selectizeHandles() {
            let fields = this.getFields();

            if (! this.fieldsContainsPartial(fields)) {
                return this.initSelectize(fields);
            }

            const url = cp_url(`/fieldsets/${get_from_segment(3)}/get?partials=true`);

            this.$http.get(url, response => {
                // Now that we have the fields inside the partial, we don't need the actual partials.
                fields = response.fields.filter(field => field.type !== 'partial');

                this.initSelectize(fields);
            });
        },

        initSelectize(fields) {
            $(this.$els.handle).selectize({
                maxItems: 1,
                options: this.cleanFields(fields),
                valueField: 'name',
                labelField: 'display',
                create: true
            });
        },

        getFields() {
            return JSON.parse(JSON.stringify(
                this.$parent.$parent.$parent.$parent.fields // todo: Do this the right way.
            ));
        },

        fieldsContainsPartial(fields) {
            return undefined !== _.find(fields, field => field.type === 'partial');
        },

        cleanFields(fields) {
            // Make sure that fields without display values show at least the handle.
            fields = fields.map(field => {
                field.display = field.display || field.name;
                return field;
            });

            // If the specified handle doesn't exist in the field list, we'll add it.
            if (this.handle && !_.find(fields, (f) => this.handle === f.name)) {
                fields.push({ name: this.handle, display: this.handle });
            }

            return fields;
        }
    }

}

</script>
