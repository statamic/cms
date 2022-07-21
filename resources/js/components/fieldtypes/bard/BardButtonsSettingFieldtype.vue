<template>
    <div class="relative">
        <div class="bard-fixed-toolbar dark bard-toolbar-setting" ref="buttons">

            <button
                v-for="button in buttons"
                :key="button.name"
                v-tooltip="button.text"
                :class="{'active': enabled(button.name)}"
                @click="toggleButton(button.name)"
            >
                <svg-icon :name="button.svg" v-if="button.svg"></svg-icon>
                <div class="flex items-center" v-html="button.html" v-if="button.html"></div>
            </button>

        </div>
    </div>
</template>

<script>
import { Sortable } from '@shopify/draggable';
import { availableButtons, addButtonHtml } from './buttons';

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
                this.data = buttons
                    .filter(button => button.enabled)
                    .map(button => button.name);
            }
        },

        data(data) {
            this.update(data);
        },

    },

    methods: {

        initButtons() {
            // Get all default buttons first
            let available = availableButtons();

            // Add custom buttons from a project or addon
            this.$bard.buttonCallbacks.map(callback => {
                // Since the developer uses the same callback to add buttons to the field itself, and for the
                // button configurator, we need to make the button conditional when on the Bard fieldtype,
                // but not here. Here we want to just show them all, so the user is able to toggle it.
                const buttonFn = (button) => button;

                let returned = callback(available, buttonFn);

                // No return value means they intend to manipulate the
                // buttons object manually. Just continue on.
                if (!returned) return;

                available = available.concat(
                    Array.isArray(returned) ? returned : [returned]
                );
            })

            available = addButtonHtml(available);

            let buttons = available.map(button => {
                button.enabled = this.data.includes(button.name);
                return button;
            });

            let standardButtons = available.map(button => button.name);
            let customButtons = this.data.filter(button => !standardButtons.includes(button));

            if (customButtons.length) {
                customButtons = customButtons.map(name => {
                    return { name, text: name, html: `<span>${name.charAt(0).toUpperCase()}</span>`, enabled: true };
                });
                buttons = [...buttons, ...customButtons];
            }

            // If the buttons have been reordered, we will remap everything to use the custom order,
            // and disabled buttons get stuck on the end. Otherwise, things will remain the same,
            // with inactive buttons dispersed throughout the toolbar, which looks cooler.
            let enabledButtonNames = buttons.filter(button => button.enabled).map(button => button.name);
            if (JSON.stringify(enabledButtonNames) !== JSON.stringify(this.data)) {
                buttons = this.data.map(name => _.findWhere(buttons, { name }));
                let unused = available.filter(button => !this.data.includes(button.name));
                buttons = [...buttons, ...unused];
            }

            this.buttons = buttons;
        },

        initSortable() {
            new Sortable(this.$refs.buttons, {
                draggable: 'button',
                delay: 200
            }).on('sortable:stop', e => {
                this.buttons.splice(e.newIndex, 0, this.buttons.splice(e.oldIndex, 1)[0]);
            });
        },

        toggleButton(name) {
            const button = _.findWhere(this.buttons, { name });
            button.enabled = !button.enabled;
        },

        enabled(name) {
            return _.findWhere(this.buttons, { name }).enabled;
        }
    }
};
</script>
