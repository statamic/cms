<script>
import Folder from './Folder.vue';

export default {

    mixins: [Folder],

    data() {
        return {
            modalTitle: __('Edit Folder'),
            buttonText: __('Update'),
        }
    },

    methods: {

        submit() {
            const url = cp_url(`asset-containers/${this.container.id}/folders/${this.path}/${this.directory}`);

            this.$axios.patch(url, { title: this.title }).then(response => {
                this.$toast.success(__('Folder updated'));
                this.$emit('updated', response.data);
            }).catch(e => {
                this.handleErrors(e);
            });
        }

    }

}
</script>
