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
                if (response.headers['content-disposition']) {
                    this.downloadFile(response);
                    this.$emit('completed', true);
                }

                // We need a blob for file downloads, but we need to convert it back to JSON to handle a redirect
                else {
                    response.data.text().then(data => {
                        data = JSON.parse(data);
                        if (data.redirect) window.location = data.redirect;
                        this.$emit('completed', true, data);
                    });
                }
            }).catch(error => {
                error.response.data.text().then(data => {
                    data = JSON.parse(data);
                    this.$toast.error(data.message);
                    if (error.response.status == 422) this.errors = data.errors;
                    this.$emit('completed', false, data)
                });
            });
        },

        downloadFile(response) {
            const attachmentMatch = response.headers['content-disposition'].match(/^attachment.+filename\*?=(?:UTF-8'')?"?([^"]+)"?/i) || [];

            if (! attachmentMatch.length) return;

            const filename = attachmentMatch.length >= 2 ? attachmentMatch[1] : 'file.txt';
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
        },

    }

}
