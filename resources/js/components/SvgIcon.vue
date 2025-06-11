<template>
    <component v-if="icon" :is="icon" />
</template>

<script>
import { defineAsyncComponent } from 'vue';
import { data_get } from  '../bootstrap/globals.js'

export default {

    props: {
        name: String,
        default: String,
        directory: String,
    },

    data() {
        return {
            icon: null,
        }
    },

    mounted() {
        this.icon = this.evaluateIcon();
    },

    watch: {
        name() {
            this.icon = this.evaluateIcon();
        }
    },

    computed: {
        customIcon() {
            if (! this.directory) return;

            let directory = this.directory;
            let folder = null;
            let file = this.name;

            if (this.name.includes('/')) {
                [folder, file] = this.name.split('/');
                directory = directory+'/'+folder;
            }

            let svgIcons = this.$config.get('customSvgIcons')[directory] ?? [];

            return svgIcons[file] ?? null;
        },
    },

    methods: {
        evaluateIcon() {
            if (this.customIcon) {
                return defineAsyncComponent(() => {
                    return new Promise(resolve => resolve({ template: this.customIcon }));
                });
            }

            if (this.name.startsWith('<svg')) {
                return defineAsyncComponent(() => {
                    return new Promise(resolve => resolve({ template: this.name }));
                });
            }

            return defineAsyncComponent(() => {
                const [set, file] = this.splitIcon(this.name);

                return import(`./../../svg/icons/${set}/${file}.svg`)
                    .catch(e => {
                        if (! this.default) return this.fallbackIconImport();
                        const [set, file] = this.splitIcon(this.default);
                        return import(`./../../svg/icons/${set}/${file}.svg`).catch(e => this.fallbackIconImport());
                    });
            });
        },

        splitIcon(icon) {
            if (! icon.includes('/')) icon = 'regular/' + icon;

            return icon.split('/');
        },

        fallbackIconImport() {
            return import('./../../svg/icons/regular/image.svg');
        },

    }
}
</script>
