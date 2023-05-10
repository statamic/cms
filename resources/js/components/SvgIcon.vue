<template>
    <component v-if="icon" :is="icon" />
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
            return defineAsyncComponent(() => {
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
