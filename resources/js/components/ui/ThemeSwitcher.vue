<template>
    <ui-button @click="toggleTheme" class="w-10">
        <svg
            v-if="isDark"
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 transform transition-transform duration-300"
            viewBox="0 0 20 20"
            fill="currentColor"
        >
            <path
                fill-rule="evenodd"
                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                clip-rule="evenodd"
            />
        </svg>
        <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 transform transition-transform duration-300"
            viewBox="0 0 20 20"
            fill="currentColor"
        >
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
        </svg>
    </ui-button>
</template>

<script>
export default {
    name: 'ThemeSwitcher',
    data() {
        return {
            isDark: false,
        };
    },
    mounted() {
        // Check if user has a theme preference stored
        const storedTheme = localStorage.getItem('theme');

        // Check if user has a system preference
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        // Set initial theme based on stored preference or system preference
        this.isDark = storedTheme === 'dark' || (!storedTheme && systemPrefersDark);

        // Apply theme on initial load
        this.applyTheme();

        // Listen for system preference changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                this.isDark = e.matches;
                this.applyTheme();
            }
        });
    },
    methods: {
        toggleTheme() {
            this.isDark = !this.isDark;
            this.applyTheme();

            // Store user preference
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        },
        applyTheme() {
            // Apply to document and add CSS transitions
            const root = document.documentElement;

            // Add transition styles if not already added
            if (!document.head.querySelector('#theme-transition-style')) {
                const style = document.createElement('style');
                style.id = 'theme-transition-style';
                style.textContent = `
        *, *::before, *::after {
        transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        }
      `;
                document.head.appendChild(style);

                // Remove the style after the transition is done
                setTimeout(() => {
                    document.head.removeChild(style);
                }, 200); // Slightly longer than the transition duration to ensure it's complete
            }

            if (this.isDark) {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
        },
    },
};
</script>
