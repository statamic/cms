import { watch, ref } from 'vue';

export default class Contrast {
    #preference;
    #contrast = ref(null);

    constructor(preference) {
        console.log('Contrast constructor called with:', preference);
        this.#preference = ref(preference ?? 'default');
        this.#watchPreferences();
        this.#watchContrast();
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
                document.documentElement.classList.toggle('contrast-more', contrast === 'more');
            },
            { immediate: true },
        );
    }

    #setContrast(preference) {
        this.#contrast.value = preference ?? 'default';
    }

    #savePreference(preference) {
        console.log('Saving contrast preference:', preference);
        if (preference === 'default') {
            Statamic.$preferences.remove('contrast');
            localStorage.removeItem('statamic.contrast');
        } else {
            Statamic.$preferences.set('contrast', preference);
            localStorage.setItem('statamic.contrast', preference);
        }
    }
} 