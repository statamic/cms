<template>
    <div class="relative">
        <div class="bard-fixed-toolbar bard-toolbar-setting dark" ref="buttons">
            <button
                v-for="button in buttons"
                :key="button.name"
                v-tooltip="button.text"
                :class="{ active: enabled(button.name) }"
                @click="toggleButton(button.name)"
            >
                <svg-icon :name="button.svg"></svg-icon>
            </button>
        </div>
    </div>
</template>

<script>
import Fieldtype from '../Fieldtype.vue';
import { Sortable, Plugins } from '@shopify/draggable';
import { availableButtons } from './commands';

export default {
    mixins: [Fieldtype],

    data() {
        return {
            data: this.value,
            buttons: [],
            autoBindChangeWatcher: false,
        };
    },

    mounted() {
        this.initButtons();
        this.initSortable();
    },

    watch: {
        buttons: {
            deep: true,
            handler(buttons) {
                const enabledButtonNames = buttons.filter((button) => button.enabled).map((button) => button.name);
                if (JSON.stringify(enabledButtonNames) !== JSON.stringify(this.data)) {
                    this.data = enabledButtonNames;
                }
            },
        },

        data(data) {
            this.update(data);
        },
    },

    methods: {
        initButtons() {
            // Get all default buttons first
            let available = availableButtons();

            let buttons = available.map((button) => {
                button.enabled = this.data.includes(button.name);
                return button;
            });

            let standardButtons = available.map((button) => button.name);
            let customButtons = this.data.filter((button) => !standardButtons.includes(button));

            if (customButtons.length) {
                customButtons = customButtons.map((name) => {
                    return { name, text: name, html: `<span>${name.charAt(0).toUpperCase()}</span>`, enabled: true };
                });
                buttons = [...buttons, ...customButtons];
            }

            // If the buttons have been reordered, we will remap everything to use the custom order,
            // and disabled buttons get stuck on the end. Otherwise, things will remain the same,
            // with inactive buttons dispersed throughout the toolbar, which looks cooler.
            let enabledButtonNames = buttons.filter((button) => button.enabled).map((button) => button.name);
            if (JSON.stringify(enabledButtonNames) !== JSON.stringify(this.data)) {
                buttons = this.data.map((name) => buttons.find((b) => b.name === name));
                let unused = available.filter((button) => !this.data.includes(button.name));
                buttons = [...buttons, ...unused];
            }

            this.buttons = buttons;
        },

        initSortable() {
            new Sortable(this.$refs.buttons, {
                draggable: 'button',
                mirror: { constrainDimensions: true, xAxis: true, appendTo: 'body' },
                swapAnimation: { horizontal: true },
                plugins: [Plugins.SwapAnimation],
                distance: 10,
            })
                .on('sortable:stop', (e) => {
                    this.buttons.splice(e.newIndex, 0, this.buttons.splice(e.oldIndex, 1)[0]);
                })
                .on('mirror:create', (e) => e.cancel());
        },

        toggleButton(name) {
            const button = this.buttons.find((b) => b.name === name);
            button.enabled = !button.enabled;
        },

        enabled(name) {
            return this.buttons.find((b) => b.name === name).enabled;
        },
    },
};
</script>
