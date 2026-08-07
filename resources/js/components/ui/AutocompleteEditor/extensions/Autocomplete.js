import Mention from '@tiptap/extension-mention';
import { VueNodeViewRenderer, VueRenderer } from '@tiptap/vue-3';
import MentionBadge from '../MentionBadge.vue';
import SuggestionList from '../SuggestionList.vue';

function renderSuggestion() {
    let component;
    let unmount;

    return {
        onStart(props) {
            component = new VueRenderer(SuggestionList, {
                props,
                editor: props.editor,
            });

            unmount = props.mount(component.element);
        },

        onUpdate(props) {
            component?.updateProps(props);
        },

        onKeyDown(props) {
            if (props.event.key === 'Escape') {
                unmount?.();
                return true;
            }

            return component?.ref?.onKeyDown(props) ?? false;
        },

        onExit() {
            unmount?.();
            unmount = undefined;
            component?.destroy();
            component = undefined;
        },
    };
}

export const Autocomplete = Mention.extend({
    name: 'mention',

    addNodeView() {
        return VueNodeViewRenderer(MentionBadge);
    },

    addOptions() {
        return {
            ...this.parent?.(),
            HTMLAttributes: { class: 'autocomplete-mention' },
            renderText({ node }) {
                return node.attrs.label ?? node.attrs.value ?? '';
            },
            renderHTML({ options, node }) {
                return ['span', options.HTMLAttributes, node.attrs.label ?? node.attrs.value ?? ''];
            },
            suggestion: {
                render: renderSuggestion,
            },
        };
    },

    addAttributes() {
        return {
            value: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-value'),
                renderHTML: (attributes) => (attributes.value ? { 'data-value': attributes.value } : {}),
            },
            label: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-label'),
                renderHTML: (attributes) => (attributes.label ? { 'data-label': attributes.label } : {}),
            },
        };
    },
});
