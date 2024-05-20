<template>
    <dropdown-list v-cloak>
            <template v-slot:trigger>
                <button class="global-header-icon-button hidden md:block"" v-tooltip="__('Theme') ">
                    <svg-icon :name="icon"></svg-icon>
                </button>
            </template>
            <dropdown-item @click="setMode('light')" class="flex items-center space-x-2">
                <svg-icon name="regular/light-mode" class="h-4 w-4"></svg-icon>
                <span>{{ __('Light') }}</span>
            </dropdown-item>
            <dropdown-item @click="setMode('dark')" class="flex items-center space-x-2">
                <svg-icon name="regular/dark-mode" class="h-4 w-4"></svg-icon>
                <span>{{ __('Dark') }}</span>
            </dropdown-item>
            <dropdown-item @click="setMode('auto')" class="flex items-center space-x-2">
                <svg-icon name="regular/system" class="h-4 w-4"></svg-icon>
                <span>{{ __('System') }}</span>
            </dropdown-item>
        </dropdown-list>
</template>

<script>
export default {
    props: {
        theme: {
            type: String,
            default: 'auto',
        }
    },
    data() {
        return {
            mode: this.theme
        }
    },
    computed: {
        icon() {
            if (this.mode === 'auto') {
                return 'regular/system'
            } else if (this.mode === 'dark') {
                return 'regular/dark-mode'
            } else {
                return 'regular/light-mode'
            }
        }
    },
    created: function() {
        const autoDark = (
            localStorage.theme === 'dark' ||
            ((!('theme' in localStorage) || localStorage.theme === 'auto') &&
            window.matchMedia('(prefers-color-scheme: dark)').matches)
        );

        if (this.theme === 'dark' || autoDark) {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
            document.documentElement.classList.remove('auto');
        } else if (this.theme === 'light' || !autoDark) {
            document.documentElement.classList.add('light');
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.remove('auto');
        }
    },
    methods: {
        setMode(mode) {
            this.mode = mode;
            localStorage.theme = mode

            if (mode === 'dark' || (mode === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                document.documentElement.classList.remove('auto');
            } else {
                document.documentElement.classList.add('light');
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.remove('auto');
            }

            this.$preferences.set('theme', mode).then(response => {
                this.$events.$emit('theme.saved');
            });
        },
    }
}
</script>
