import { sortBy } from 'lodash-es';
import axios from 'axios';

export default function useActions() {
    function sortActions(actions) {
        let sorted = sortBy(actions, 'title');

        return [
            ...sorted.filter((action) => !action.dangerous),
            ...sorted.filter((action) => action.dangerous),
        ];
    }

    function runServerAction({ url, selections, action, values, done }) {
        return new Promise((resolve, reject) => {
            const payload = {
                action: action.handle,
                context: action.context,
                selections,
                values,
            };

            // Note: A blob response type is required for file downloads,
            // but we can use handlers to JSON.parse all non-file responses...
            axios
                .post(url, payload, { responseType: 'blob' })
                .then(response => {
                    response.headers['content-disposition']
                        ? handleFileDownload(response, resolve)
                        : handleActionSuccess(response, resolve);
                })
                .catch(error => handleActionError(error.response, reject))
                .finally(() => {
                    if (done) done();
                });
        });
    }

    function handleActionSuccess(response, resolve) {
        response.data.text().then((data) => {
            data = JSON.parse(data);

            if (data.redirect) {
                if (data.bypassesDirtyWarning) this.$dirty.disableWarning();
                window.location = data.redirect;
            }

            if (data.callback) {
                Statamic.$callbacks.call(data.callback[0], ...data.callback.slice(1));
            }

            resolve(data);
        });
    }

    function handleFileDownload(response, resolve) {
        const attachmentMatch = response
            .headers['content-disposition']
            .match(/^attachment.+filename\*?=(?:UTF-8'')?"?([^"]+)"?/i) || [];

        if (!attachmentMatch.length) return;

        const filename = attachmentMatch.length >= 2 ? attachmentMatch[1] : 'file.txt';
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();

        resolve();
    }

    function handleActionError(response, reject) {
        response.data.text().then((data) => {
            data = JSON.parse(data);
            // TODO: Pass errors through reject() and toast in actual component
            // if (response.status == 422) errors.value = data.errors;
            // this.$toast.error(data.message);
            reject(data);
        });
    }

    return {
        sortActions,
        runServerAction,
    };
}
