<script>
import Folder from './Folder.vue';

export default {

    mixins: [Folder],

    data() {
        return {
            modalTitle: __('Create Folder'),
            buttonText: __('Create'),
        }
    },

    methods: {

        submit() {
            const url = cp_url(`asset-containers/${this.container.id}/folders`);
            const payload = {
                path: this.path,
                directory: this.directory,
                title: this.title
            };

            this.$axios.post(url, payload).then(response => {
                this.$toast.success(__('Folder created'));
                this.$emit('created', response.data);
            }).catch(e => {
                this.handleErrors(e);
            });
        }

    }

}
</script>
