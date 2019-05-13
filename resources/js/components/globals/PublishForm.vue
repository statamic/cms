<script>
import ConfigureSet from './Configure.vue';

export default {

    components: {
        ConfigureSet
    },

    props: {
        initialFieldset: Object,
        initialValues: Object,
        initialMeta: Object,
        action: String
    },

    data() {
        return {
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
        }

    },

    created() {
        this.fieldset = this.initialFieldset;
    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.clearErrors();

            this.$axios.patch(this.action, this.values).then(response => {
                this.$notify.success('Saved');
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$notify.error(message);
                } else {
                    this.$notify.error(e.response ? e.response.data.message : __('Something went wrong'));
                }
            });
        }

    },

    mounted() {
        this.$mousetrap.bindGlobal(['command+s'], e => {
            e.preventDefault();
            this.save();
        });
    }

}
</script>
