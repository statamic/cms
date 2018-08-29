<script>
import axios from 'axios';

export default {

    props: {
        fieldset: Object,
        initialValues: Object,
        action: String
    },

    data() {
        return {
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

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.clearErrors();

            axios.patch(this.action, this.values).then(response => {
                // this.$notify.success('Saved!');
                alert('Saved!');
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                } else {
                    // this.$notify.error('Something went wrong');
                    alert('Something went wrong');
                }
            });
        }

    }

}
</script>
