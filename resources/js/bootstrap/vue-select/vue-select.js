import { h } from 'vue';
import VueSelect from 'vue-select';
import OpenChevron from './OpenChevron.vue';

export default function registerVueSelect(app) {
    // Customize vSelect UI components
    VueSelect.props.components.default = () => ({
        Deselect: {
            render: () => h('span', __('×')),
        },
        OpenIndicator: {
            render: () =>
                h(
                    'span',
                    {
                        class: { toggle: true },
                    },
                    h(OpenChevron),
                ),
        },
    });

    app.component('v-select', VueSelect);
}
