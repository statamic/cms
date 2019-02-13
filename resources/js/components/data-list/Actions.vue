<script>
import axios from 'axios';
import DataListAction from './Action.vue';

export default {

    components: {
        DataListAction
    },

    props: {
        actions: Array,
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

            axios.post(this.url, payload).then(response => {
                this.$emit('completed');
            }).catch(error => {
                this.$notify.error(error.response.data.message);
            });
        }

    }

}
</script>
