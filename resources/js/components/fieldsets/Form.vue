<script>
import FieldsetFields from './Fields.vue';

export default {

    components: {
        FieldsetFields
    },

    props: ['action', 'initialFieldset'],

    data() {
        return {
            fieldset: JSON.parse(JSON.stringify(this.initialFieldset)),
            errors: {}
        }
    },

    methods: {

        save() {
            this.$axios[this.method](this.action, this.fieldset)
                .then(response => this.saved(response))
                .catch(e => {
                    this.$notify.error(e.response.data.message);
                    this.errors = e.response.data.errors;
                })
        },

        fieldsUpdated(fields) {
            this.fieldset.fields = fields;
        }

    }

}
</script>
