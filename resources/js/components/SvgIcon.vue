<template>
    <component v-if="icon" :is="icon" />
</template>

<script>
import { defineAsyncComponent } from 'vue';

const fallbackIconImport = function() {
    return import('./../../svg/icons/regular/image.svg');
}

export default {
    props: {
        name: String,
        default: String,
        directory: String,
        folder: String,
    },
    data() {
        return {
            icon: this.evaluateIcon(),
        }
    },
    watch: {
        name() {
            this.icon = this.evaluateIcon();
        }
    },

    methods: {

        evaluateIcon() {
            if (this.name.startsWith('<svg')) {
                return defineAsyncComponent(() => {
                    return new Promise(resolve => resolve({ template: this.name }));
                });
            }

            return defineAsyncComponent(() => {
                console.log(this.getIconPath(this.name));
                return import(this.getIconPath(this.name))
                    .catch(e => {
                        if (! this.default) return fallbackIconImport();
                        return import(this.getIconPath(this.default)).catch(e => fallbackIconImport());
                    });
            });
        },

        getIconPath(name) {
            return this.$config.get('test-path');
            let directory = this.directory ? `./../../../../${this.directory}` : './../../svg/icons';
            let folder = this.folder || 'regular';

            // If a legacy `plump/icon` name string is passed instead of using dedicated folder
            // prop, parse the folder and file from the icon name as we have always done.
            if (name.includes('/')) {
                folder = name.split('/')[0];
                name = name.split('/')[1];
            }

            console.log(`${directory}/${folder}/${name}.svg`);
            return `${directory}/${folder}/${name}.svg`;
        },

    }
}
</script>
