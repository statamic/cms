<template>
    <ui-toggle-group variant="ghost" size="xs" class="justify-between" v-model="preference">
        <ui-toggle-item icon="sun" class="[&_svg]:size-4.5" value="light" :label="__('Light')" />
        <ui-toggle-item icon="moon" class="[&_svg]:size-4.5" value="dark" :label="__('Dark')" />
        <ui-toggle-item icon="monitor" class="[&_svg]:size-4.5" value="auto" :label="__('System')" />
    </ui-toggle-group>
</template>

<script>
export default {
    props: {
        initial: { type: String, default: 'auto' },
    },
    data() {
        return {
            preference: this.initial,
            theme: null,
        };
    },
    watch: {
        preference: {
            immediate: true,
            handler(mode) {
                this.theme = mode === 'dark' || (mode === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)
                    ? 'dark'
                    : 'light';

                if (mode === 'auto') {
                    this.$preferences.remove('theme');
                    localStorage.removeItem('statamic.theme');
                } else {
                    this.$preferences.set('theme', mode);
                    localStorage.setItem('statamic.theme', mode);
                }
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
};
</script>
