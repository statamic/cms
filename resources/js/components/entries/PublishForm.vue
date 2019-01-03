<script>
import axios from 'axios';
import Fieldset from '../publish/Fieldset';

export default {

    props: {
        initialFieldset: Object,
        initialValues: Object,
        initialMeta: Object,
        action: String,
        method: String
    },

    data() {
        return {
            saving: false,
            fieldset: null,
            values: _.clone(this.initialValues),
            meta: _.clone(this.initialMeta),
            error: null,
            errors: {}
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },

        canSave() {
            return this.$progress.isComplete();
        }

    },

    created() {
        this.fieldset = new Fieldset(this.initialFieldset)
            .showSlug(true)
            .prependTitle()
            .prependMeta()
            .getFieldset();
    },

    watch: {

        saving(saving) {
            this.$progress.loading('entry-publish-form', saving);
        }

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.saving = true;
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
            }).finally(() => {
                this.saving = false;
            });
        }

    }

}
</script>
