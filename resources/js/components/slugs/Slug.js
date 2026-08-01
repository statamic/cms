import axios from 'axios';
import speakingUrl from 'speakingurl';
import debounce from '@/util/debounce.js';

export default class Slug {
    busy = false;
    #string;
    #separator = '-';
    #language;
    #debounced;
    #controller;
    #async = false;
    #preservePaths = false;

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

    preservePaths(preserve = true) {
        this.#preservePaths = preserve;

        return this;
    }

    #setInitialLanguage() {
        const selectedSite = Statamic.$config.get('selectedSite');
        const sites = Statamic.$config.get('sites');
        const site = sites.find((site) => site.handle === selectedSite);
        this.#language = site?.lang ?? Statamic.$config.get('lang');
    }

    async() {
        this.#async = true;

        this.#debounced = debounce(function (resolve, reject) {
            return this.#performRequest()
                .then((slug) => resolve(slug))
                .catch((e) => reject(e));
        }, 300);

        return this;
    }

    create(string) {
        this.#string = (string + '').trim();

        return this.#async ? this.#createAsynchronously() : this.#createSynchronously();
    }

    #createSynchronously() {
        if (this.#preservePaths) {
            return this.#string
                .split('/')
                .map((segment) => this.#slugifySegment(segment))
                .filter((segment) => segment !== '')
                .join('/');
        }

        return this.#slugifySegment(this.#string);
    }

    #slugifySegment(string) {
        if (this.#preservePaths && string === '_index') {
            return '_index';
        }

        const symbols = Statamic.$config.get('asciiReplaceExtraSymbols');
        const charmap = Statamic.$config.get('charmap');

        let custom = charmap[this.#language] ?? {};
        custom["'"] = ''; // Remove apostrophes in all languages
        custom['’'] = ''; // Remove smart single quotes
        custom[' - '] = ' '; // Prevent `Block - Hero` turning into `block_-_hero`
        custom['('] = ''; // Remove parentheses
        custom[')'] = ''; // Remove parentheses
        custom = symbols ? this.#replaceCurrencySymbols(custom, charmap) : this.#removeCurrencySymbols(custom, charmap);

        if (this.#separator !== '-') custom['-'] = this.#separator; // Replace dashes with custom separator

        return speakingUrl(string, {
            separator: this.#separator,
            lang: this.#language,
            custom,
            symbols,
        });
    }

    #replaceCurrencySymbols(custom, charmap) {
        return { ...custom, ...charmap.currency };
    }

    #removeCurrencySymbols(custom, charmap) {
        for (const key in charmap.currency_short) {
            custom[key] = '';
        }

        return custom;
    }

    #createAsynchronously() {
        if (!this.#string) {
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
            language: this.#language,
            preserve_paths: this.#preservePaths,
        };

        if (this.#controller) this.#controller.abort();
        this.#controller = new AbortController();

        let aborted = false;
        return axios
            .post(cp_url('slug'), payload, { signal: this.#controller.signal, transformResponse: [(data) => data] })
            .then((response) => response.data)
            .catch((e) => {
                if (axios.isCancel(e)) {
                    aborted = true;
                }
                throw e;
            })
            .finally(() => {
                if (!aborted) this.busy = false;
            });
    }
}
