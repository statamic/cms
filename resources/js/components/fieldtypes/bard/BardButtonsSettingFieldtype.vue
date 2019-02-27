<template>
    <div class="relative">
        <div class="bard-toolbar bard-toolbar-setting" v-el:buttons>

            <button
                v-for="button in buttons"
                :key="button.name"
                v-tip :tip-text="button.text"
                :class="{'active': enabled(button.name)}"
                @click="toggleButton(button.name)"
                v-html="button.html"
            ></button>

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
            buttons: [],
            autoBindChangeWatcher: false,
        };
    },

    ready: function() {
        if ( ! this.data) {
            this.data = this.config.default || [];
        }

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
        }

    },

    methods: {

        initButtons() {
            let available = addButtonHtml(availableButtons);

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
            new Sortable(this.$els.buttons, {
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
