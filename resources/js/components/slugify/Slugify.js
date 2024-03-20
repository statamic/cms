import axios from 'axios';

export default class Slugify {
    busy = false;
    #string;
    #separator = '-';
    #language;
    #debounced;
    #controller;

    constructor() {
        this.#setInitialLanguage();
        this.#debounced = _.debounce(function (resolve) {
            this.#performRequest().then(slug => resolve(slug));
        }, 300)
    }

    separatedBy(separator) {
        this.#separator = separator;

        return this;
    }

    in(language) {
        this.#language = language;

        return this;
    }

    #setInitialLanguage() {
        const selectedSite = Statamic.$config.get('selectedSite');
        const sites = Statamic.$config.get('sites');
        const site = sites.find(site => site.handle === selectedSite);
        this.#language = site?.lang ?? Statamic.$config.get('lang');
    }

    create(string) {
        this.busy = true;
        this.#string = string;

        return new Promise(resolve => this.#debounced(resolve));
    }

    #performRequest() {
        const payload = {
            string: this.#string,
            separator: this.#separator,
            language: this.#language
        };

        if (this.#controller) this.#controller.abort();
        this.#controller = new AbortController;

        let aborted = false;
        return axios.post(cp_url('slug'), payload, { signal: this.#controller.signal })
            .then(response => response.data)
            .catch(e => {
                if (axios.isCancel(e)) {
                    aborted = true;
                    return;
                }
                throw e;
            })
            .finally(() => {
                if (!aborted) this.busy = false
            });
    }
}
