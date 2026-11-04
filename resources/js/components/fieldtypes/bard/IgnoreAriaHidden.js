import { Extension } from '@tiptap/core';
import { Plugin } from '@tiptap/pm/state';

// Modal overlays aria-hide the page around [aria-live] and script elements, so one inside a set marks the
// editor's blocks. ProseMirror would redraw them and the sets with them; it has no public hook to prevent that.
const attributes = ['aria-hidden', 'data-aria-hidden'];

export const IgnoreAriaHidden = Extension.create({
    name: 'ignoreAriaHidden',

    addProseMirrorPlugins() {
        return [
            new Plugin({
                view({ domObserver: observer }) {
                    if (!observer?.registerMutation) return {};

                    const registerMutation = observer.registerMutation.bind(observer);
                    observer.registerMutation = (mutation, added) =>
                        mutation.type === 'attributes' && attributes.includes(mutation.attributeName)
                            ? null
                            : registerMutation(mutation, added);

                    return { destroy: () => delete observer.registerMutation };
                },
            }),
        ];
    },
});
