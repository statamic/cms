<template>
    <component v-if="icon" :is="icon" />
</template>

<script>
import { defineAsyncComponent } from 'vue';

const splitIcon = function(icon) {
    if (! icon.includes('/')) icon = 'default/' + icon;
    return icon.split('/');
}

export default {
    props: {
        name: String,
        default: String,
    },
    computed: {
        icon() {
            return defineAsyncComponent(() => {
                const [set, file] = splitIcon(this.name);
                return import(`./../../svg/icons/${set}/${file}.svg`)
                    .catch(e => {
                        const [set, file] = splitIcon(this.default);
                        return import(`./../../svg/icons/${set}/${file}.svg`)
                            .catch(e => import('./../../svg/icons/default/image.svg'))
                    });
            });
        }
    }
}
</script>
