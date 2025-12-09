import { watch, ref } from 'vue';

export default class ColorMode {
    #preference;
    #mode = ref(null);

    initialize(preference) {
        this.#preference = ref(preference);
        this.#setMode(preference);
        this.#watchPreferences();
        this.#watchMode();
        this.#listenForColorSchemeChange();
        this.#registerCommands();
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
                this.#setMode(preference);
                this.#savePreference(preference);
            }
        );
    }

    #watchMode() {
        watch(
            this.#mode,
            (mode) => {
                document.documentElement.classList.toggle('dark', mode === 'dark');
            },
            { immediate: true },
        );
    }

    #listenForColorSchemeChange() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (this.#preference.value === 'auto') this.#mode.value = e.matches ? 'dark' : 'light';
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'statamic.color_mode') this.#mode.value = e.newValue;
        });
    }

    #setMode(preference) {
        this.#mode.value =
            preference === 'dark' ||
            (preference === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)
                ? 'dark'
                : 'light';
    }

    #savePreference(preference) {
        if (preference === 'auto') {
            if (Statamic.$config.get('user') && Statamic.$preferences.has('color_mode')) {
                Statamic.$preferences.remove('color_mode');
            }

            localStorage.removeItem('statamic.color_mode');
        } else {
            if (Statamic.$config.get('user') && Statamic.$preferences.get('color_mode') !== preference) {
                Statamic.$preferences.set('color_mode', preference);
            }

            localStorage.setItem('statamic.color_mode', preference);
        }
    }

    #registerCommands() {
        Statamic.$commandPalette.add({
            text: [__('Toggle Color Mode'), __('Light')],
            icon: 'sun',
            action: () => {
                this.preference = 'light';
            },
            persist: true,
        });

        Statamic.$commandPalette.add({
            text: [__('Toggle Color Mode'), __('Dark')],
            icon: 'moon',
            action: () => {
                this.preference = 'dark';
            },
            persist: true,
        });

        Statamic.$commandPalette.add({
            text: [__('Toggle Color Mode'), __('System')],
            icon: 'monitor',
            action: () => {
                this.preference = 'auto';
            },
            persist: true,
        });
    }
}
