import { watch, ref } from 'vue';

export default class Theme {
    #preference;
    #theme = ref(null);

    constructor(preference) {
        this.#preference = ref(preference);
        this.#watchPreferences();
        this.#watchTheme();
        this.#listenForColorSchemeChange();
    }

    get preference() {
        return this.#preference;
    }

    set preference(value) {
        this.#preference.value = value;
    }

    #watchPreferences() {
        watch(
            this.#preference,
            (preference) => {
                this.#setTheme(preference);
                this.#savePreference(preference);
            },
            { immediate: true },
        );
    }

    #watchTheme() {
        watch(
            this.#theme,
            (theme) => {
                document.documentElement.classList.toggle('dark', theme === 'dark');
            },
            { immediate: true },
        );
    }

    #listenForColorSchemeChange() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (this.#preference.value === 'auto') this.#theme.value = e.matches ? 'dark' : 'light';
        });
    }

    #setTheme(preference) {
        this.#theme.value =
            preference === 'dark' ||
            (preference === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)
                ? 'dark'
                : 'light';
    }

    #savePreference(preference) {
        if (preference === 'auto') {
            Statamic.$preferences.remove('theme');
            localStorage.removeItem('statamic.theme');
        } else {
            Statamic.$preferences.set('theme', preference);
            localStorage.setItem('statamic.theme', preference);
        }
    }
}
