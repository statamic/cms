<template>
    <dropdown-list v-cloak>
        <template v-slot:trigger>
            <button class="global-header-icon-button hidden md:block" v-tooltip="__('Theme')">
                <svg-icon :name="icon"></svg-icon>
            </button>
        </template>
        <dropdown-item @click="prefer('light')" class="flex items-center space-x-2 rtl:space-x-reverse">
            <svg-icon name="regular/light-mode" class="h-4 w-4"></svg-icon>
            <span>{{ __('Light') }}</span>
        </dropdown-item>
        <dropdown-item @click="prefer('dark')" class="flex items-center space-x-2 rtl:space-x-reverse">
            <svg-icon name="regular/dark-mode" class="h-4 w-4"></svg-icon>
            <span>{{ __('Dark') }}</span>
        </dropdown-item>
        <dropdown-item @click="prefer('auto')" class="flex items-center space-x-2 rtl:space-x-reverse">
            <svg-icon name="regular/system" class="h-4 w-4"></svg-icon>
            <span>{{ __('System') }}</span>
        </dropdown-item>
    </dropdown-list>
</template>

<script>
export default {
    props: {
        initial: {
            type: String,
            default: 'auto',
        },
    },
    data() {
        return {
            preference: this.initial, // dark, light, auto
            theme: null, // dark, light
        };
    },
    computed: {
        icon() {
            if (this.preference === 'auto') {
                return 'regular/system';
            } else if (this.preference === 'dark') {
                return 'regular/dark-mode';
            } else {
                return 'regular/light-mode';
            }
        },
    },
    watch: {
        preference: {
            immediate: true,
            handler(mode) {
                this.theme =
                    mode === 'dark' || (mode === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)
                        ? 'dark'
                        : 'light';
            },
        },
        theme: {
            immediate: true,
            handler(theme) {
                document.documentElement.classList.toggle('dark', theme === 'dark');
                Statamic.darkMode = theme === 'dark';
            },
        },
    },
    created() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (this.preference === 'auto') this.theme = e.matches ? 'dark' : 'light';
        });
    },
    methods: {
        prefer(mode) {
            this.preference = mode;

            // Saving to user preference allows it to persist across browsers, whereas saving to local
            // storage allows it to work before the user is authenticated, e.g. on the login screen.
            if (mode === 'auto') {
                this.$preferences.remove('theme');
                localStorage.removeItem('statamic.theme');
            } else {
                this.$preferences.set('theme', mode);
                localStorage.setItem('statamic.theme', mode);
            }
        },
    },
};
</script>
