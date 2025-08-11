import { watch, ref } from 'vue';

export default class Contrast {
    #preference;
    #contrast = ref(null);

    constructor(preference) {
        this.#preference = ref(preference ? 'increased' : 'auto');
        this.#setContrast(this.#preference.value);
        this.#watchContrast();
        this.#listenForColorSchemeChange();
    }

    #watchContrast() {
        watch(
            this.#contrast,
            (contrast) => document.body.setAttribute('data-contrast', contrast),
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
}
