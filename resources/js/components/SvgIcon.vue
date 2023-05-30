<template>
    <component v-if="iconComponent" :is="iconComponent" />
    <span v-else-if="svgIcon" v-html="svgIcon" />
</template>

<script>
import { defineAsyncComponent } from 'vue';

const splitIcon = function(icon) {
    if (! icon.includes('/')) icon = 'regular/' + icon;
    return icon.split('/');
}

const fallbackIconImport = function() {
    return import('./../../svg/icons/regular/image.svg');
}

export default {
    props: {
        name: String,
        default: String,
    },
    data() {
        return {
            iconComponent: null,
            svgIcon: null,
        }
    },
    mounted() {
        this.handleIcon()
    },
    watch: {
        name() {
            this.handleIcon();
        },
    },
    methods: {
        handleIcon() {
            if (this.name.startsWith('<svg')) {
                return this.svgIcon = this.name;
            }

            return this.iconComponent = defineAsyncComponent(() => {
                const [set, file] = splitIcon(this.name);
                return import(`./../../svg/icons/${set}/${file}.svg`)
                    .catch(e => {
                        if (! this.default) return fallbackIconImport();
                        const [set, file] = splitIcon(this.default);
                        return import(`./../../svg/icons/${set}/${file}.svg`).catch(e => fallbackIconImport());
                    });
            });
        }
    }
}
</script>
