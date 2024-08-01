import axios from 'axios';
import speakingUrl from 'speakingurl';

export default class Slug {
    busy = false;
    #string;
    #separator = '-';
    #language;
    #debounced;
    #controller;
    #async = false;

    constructor() {
        this.#setInitialLanguage();
    }

    separatedBy(separator) {
        if (separator) this.#separator = separator;

        return this;
    }

    in(language) {
        if (language) this.#language = language;

        return this;
    }

    #setInitialLanguage() {
        const selectedSite = Statamic.$config.get('selectedSite');
        const sites = Statamic.$config.get('sites');
        const site = sites.find(site => site.handle === selectedSite);
        this.#language = site?.lang ?? Statamic.$config.get('lang');
    }

    async() {
        this.#async = true;

        this.#debounced = _.debounce(function (resolve, reject) {
            return this.#performRequest()
                .then(slug => resolve(slug))
                .catch(e => reject(e));
        }, 300)

        return this;
    }

    create(string) {
        this.#string = (string + '').trim();

        return this.#async
            ? this.#createAsynchronously()
            : this.#createSynchronously();
    }

    #createSynchronously() {
        const custom = Statamic.$config.get(`charmap.${this.#language}`) ?? {};
        custom["'"] = ""; // Remove apostrophes in all languages
        custom["’"] = ""; // Remove smart single quotes
        custom[" - "] = " "; // Prevent `Block - Hero` turning into `block_-_hero`

        return speakingUrl(this.#string, {
            separator: this.#separator,
            lang: this.#language,
            custom,
            symbols: Statamic.$config.get('asciiReplaceExtraSymbols')
        });
    }

    #createAsynchronously() {
        if (! this.#string) {
            this.#controller?.abort();
            this.#debounced.cancel();
            this.busy = false;
            return Promise.resolve('');
        }

        this.busy = true;

        return new Promise((resolve, reject) => this.#debounced(resolve, reject));
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
