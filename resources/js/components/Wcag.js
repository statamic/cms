import { watch, ref } from 'vue';

export default class Wcag {
    #preference;
    #wcagEnabled = ref(false);

    constructor(preference) {
        this.#preference = ref(preference ?? false);
        this.#watchPreferences();
        this.#watchWcag();
    }

    get preference() {
        return this.#preference;
    }

    set preference(value) {
        this.#preference.value = value;
    }

    get enabled() {
        return this.#wcagEnabled;
    }

    #watchPreferences() {
        watch(
            this.#preference,
            (preference) => {
                this.#setWcag(preference);
                this.#savePreference(preference);
            },
            { immediate: true },
        );
    }

    #watchWcag() {
        watch(
            this.#wcagEnabled,
            (enabled) => {
                document.documentElement.classList.toggle('wcag-conformity', enabled);
            },
            { immediate: true },
        );
    }

    #setWcag(preference) {
        this.#wcagEnabled.value = !!preference;
    }

    #savePreference(preference) {
        if (!preference) {
            Statamic.$preferences.remove('wcag_conformity');
            localStorage.removeItem('statamic.wcag_conformity');
        } else {
            Statamic.$preferences.set('wcag_conformity', preference);
            localStorage.setItem('statamic.wcag_conformity', preference);
        }
    }
} 