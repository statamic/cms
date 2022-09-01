import DataListAction from './Action.vue';

export default {

    components: {
        DataListAction
    },

    props: {
        url: String
    },

    data() {
        return {
            errors: {}
        }
    },

    computed: {

        sortedActions() {
            let actions = _.sortBy(this.actions, 'title');

            return [
                ...actions.filter(action => !action.dangerous),
                ...actions.filter(action => action.dangerous)
            ];
        },

    },

    methods: {

        run(action, values) {
            this.$emit('started');

            this.errors = {};

            const payload = {
                action: action.handle,
                context: action.context,
                selections: this.selections,
                values
            };

            this.$axios.post(this.url, payload, { responseType: 'blob' }).then(response => {
                response.data.text().then(data => {
                    response.headers['content-disposition']
                        ? this.handleFileDownload(response) // Pass blob response for downloads
                        : this.handleActionSuccess(JSON.parse(data)); // Otherwise convert to JSON and handle as normal
                });
            }).catch(error => {
                error.response.data.text().then(data => {
                    this.handleActionError(JSON.parse(data), error.response.status);
                });
            });
        },

        handleActionSuccess(response) {
            if (response.redirect) window.location = response.redirect;
            if (response.callback) Statamic.$callbacks.call(response.callback[0], ...response.callback.slice(1));
            this.$emit('completed', true, response);
        },

        handleActionError(response, status) {
            if (status == 422) this.errors = response.errors;
            this.$toast.error(response.message);
            this.$emit('completed', false, response)
        },

        handleFileDownload(response) {
            const attachmentMatch = response.headers['content-disposition'].match(/^attachment.+filename\*?=(?:UTF-8'')?"?([^"]+)"?/i) || [];
            if (! attachmentMatch.length) return;
            const filename = attachmentMatch.length >= 2 ? attachmentMatch[1] : 'file.txt';
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            this.$emit('completed', true);
        },

    }

}
