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

            this.$axios.post(this.url, payload, { responseType: 'blob' }).then(response => {
                this.$emit('completed');

                if (response.headers['content-disposition']) {
                    this.downloadFileFromResponse(response)
                }
            }).catch(error => {
                this.$toast.error(error.response.data.message);
                this.$emit('completed');
            });
        },

        downloadFileFromResponse(response) {
            const attachmentMatch = response.headers['content-disposition'].match(/^attachment.+filename="([^"]+)"/i)
            if (attachmentMatch && attachmentMatch.length) {
                const filename = attachmentMatch.length >= 2 ? attachmentMatch[1] : 'file.txt'
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', filename);
                document.body.appendChild(link);
                link.click();
            }
        }

    }

}
</script>
