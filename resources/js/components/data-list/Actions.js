import DataListAction from './Action.vue';
import { sortBy } from 'lodash-es';

export default {
    components: {
        DataListAction,
    },

    props: {
        url: String,
    },

    data() {
        return {
            errors: {},
        };
    },

    computed: {
        sortedActions() {
            let actions = sortBy(this.actions, 'title');

            return [...actions.filter((action) => !action.dangerous), ...actions.filter((action) => action.dangerous)];
        },
    },

    methods: {
        run(action, values, done) {
            this.$emit('started');

            this.errors = {};

            const payload = {
                action: action.handle,
                context: action.context,
                selections: this.selections,
                values,
            };

            this.$axios
                .post(this.url, payload, { responseType: 'blob' })
                .then((response) => {
                    response.headers['content-disposition']
                        ? this.handleFileDownload(response) // Pass blob response for downloads
                        : this.handleActionSuccess(response); // Otherwise handle as normal, converting from JSON
                })
                .catch((error) => this.handleActionError(error.response))
                .finally(() => {
                    if (done) done();
                });
        },

        handleActionSuccess(response) {
            response.data.text().then((data) => {
                data = JSON.parse(data);
                if (data.redirect) {
                    if (data.bypassesDirtyWarning) this.$dirty.disableWarning();
                    window.location = data.redirect;
                }
                if (data.callback) Statamic.$callbacks.call(data.callback[0], ...data.callback.slice(1));
                this.$emit('completed', true, data);
            });
        },

        handleActionError(response) {
            response.data.text().then((data) => {
                data = JSON.parse(data);
                if (response.status == 422) this.errors = data.errors;
                this.$toast.error(data.message);
                this.$emit('completed', false, data);
            });
        },

        handleFileDownload(response) {
            const attachmentMatch =
                response.headers['content-disposition'].match(/^attachment.+filename\*?=(?:UTF-8'')?"?([^"]+)"?/i) ||
                [];
            if (!attachmentMatch.length) return;
            const filename = attachmentMatch.length >= 2 ? attachmentMatch[1] : 'file.txt';
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            this.$emit('completed', true);
        },
    },
};
