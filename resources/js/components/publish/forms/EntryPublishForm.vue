<script>
import axios from 'axios';
import Fieldset from '../Fieldset';

export default {

    props: {
        initialFieldset: Object,
        initialValues: Object,
        action: String
    },

    data() {
        return {
            fieldset: null,
            values: _.clone(this.initialValues),
            error: null,
            errors: {}
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        }

    },

    created() {
        this.fieldset = new Fieldset(this.initialFieldset)
            .showSlug(true)
            .prependTitle()
            .prependMeta()
            .getFieldset();
    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.clearErrors();

            axios.patch(this.action, this.values).then(response => {
                this.$notify.success('Saved');
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$notify.error(message, { timeout: 2000 });
                } else {
                    this.$notify.error('Something went wrong');
                }
            });
        }

    }

}
</script>
