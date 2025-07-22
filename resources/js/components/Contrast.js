import { watch, ref } from 'vue';

export default class Contrast {
    #preference;
    #contrast = ref(null);

    constructor(preference) {
        this.#preference = ref(preference ?? 'default');
        this.#watchPreferences();
        this.#watchContrast();
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
                this.#setContrast(preference);
                this.#savePreference(preference);
            },
            { immediate: true },
        );
    }

    #watchContrast() {
        watch(
            this.#contrast,
            (contrast) => {
                document.documentElement.classList.toggle('contrast-increased', contrast === 'increased');
            },
            { immediate: true },
        );
    }

    #setContrast(preference) {
        this.#contrast.value = preference === 'increased' ||
            (preference === 'auto' && window.matchMedia('(prefers-contrast: more)').matches)
                ? 'increased'
                : 'default';
    }

    #listenForColorSchemeChange() {
        window.matchMedia('(prefers-contrast: more)').addEventListener('change', (e) => {
            if (this.#preference.value === 'auto') this.#contrast.value = e.matches ? 'increased' : 'default';
        });
    }

    #savePreference(preference) {
        if (preference === 'default') {
            Statamic.$preferences.remove('contrast');
            localStorage.removeItem('statamic.contrast');
        } else {
            Statamic.$preferences.set('contrast', preference);
            localStorage.setItem('statamic.contrast', preference);
        }
    }
} 