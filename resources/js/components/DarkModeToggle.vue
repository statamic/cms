<template>
    <label class="dark-mode-toggle">
        <input v-model="darkMode" :checked="darkMode" @change="toggleDarkMode" class="toggle-checkbox" type="checkbox" />
        <div class="toggle-slot">
            <div class="sun-icon-wrapper">
                <svg aria-hidden="true" class="sun-icon" data-icon="feather-sun" data-inline="false" focusable="false" height="1em" preserveAspectRatio="xMidYMid meet"
                     style="transform: rotate(360deg)" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path>
                    </g>
                </svg>
            </div>
            <div class="toggle-button"></div>
            <div class="moon-icon-wrapper">
                <svg aria-hidden="true" class="moon-icon" data-icon="feather-moon" data-inline="false" focusable="false" height="1em" preserveAspectRatio="xMidYMid meet"
                     style="transform: rotate(360deg)" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3A7 7 0 0 0 21 12.79z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                </svg>
            </div>
        </div>
    </label>
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
            darkMode: false
        }
    },
    created: function() {
        const autoDark = (localStorage.theme === 'dark') || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
        if (this.theme === 'dark' || autoDark) {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
            document.documentElement.classList.remove('auto');
            this.darkMode = true;
        } else if (this.theme === 'light' || !autoDark) {
            document.documentElement.classList.add('light');
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.remove('auto');
            this.darkMode = false;
        }
    },
    methods: {
        toggleDarkMode () {
            if (this.darkMode) {
                localStorage.theme = 'dark'
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                document.documentElement.classList.remove('auto');
            } else {
                localStorage.theme = 'light'
                document.documentElement.classList.add('light');
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.remove('auto');
            }
            this.$preferences.set('theme', this.darkMode ? 'dark' : 'light').then(response => {
                this.$events.$emit('theme.saved');
            });
        }
    }
}
</script>
