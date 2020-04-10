<script>
import DataListAction from './Action.vue';

export default {

    components: {
        DataListAction
    },

    props: {
        url: String
    },

    computed: {

        sortedActions() {
            return [
                ...this.actions.filter(action => !action.dangerous),
                ...this.actions.filter(action => action.dangerous)
            ];
        },

    },

    methods: {

        run(action, values) {
            this.$emit('started');

            const payload = {
                action: action.handle,
                context: action.context,
                selections: this.selections,
                values
            };

            this.$axios.post(this.url, payload).then(response => {
                this.$emit('completed');

                if (response.data.redirect) {
                    window.location = response.data.redirect;
                }
            }).catch(error => {
                this.$toast.error(error.response.data.message);
                this.$emit('completed');
            });
        }

    }

}
</script>
