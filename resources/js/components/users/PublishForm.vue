<script>
import axios from 'axios';
import ChangePassword from './ChangePassword.vue';

export default {

    components: {
        ChangePassword,
    },

    props: {
        initialFieldset: Object,
        initialValues: Object,
        action: String,
        method: String
    },

    data() {
        return {
            fieldset: _.clone(this.initialFieldset),
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

            axios[this.method](this.action, this.values).then(response => {
                this.$notify.success('Saved');
                const redirect = response.data.redirect;
                if (redirect) window.location = redirect;
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
