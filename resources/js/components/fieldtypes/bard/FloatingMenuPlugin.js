import { posToDOMRect } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';

class FloatingMenuView {
    constructor({ editor, element, view, shouldShow, vm }) {
        this.editor = editor;
        this.element = element;
        this.view = view;
        this.vm = vm;
        this.shouldShow = shouldShow;

        this.editor.on('focus', this.focusHandler);
    }

    focusHandler = () => {
        this.update(this.editor.view);
    };

    update(view, oldState) {
        const { state } = view;
        const { doc, selection } = state;
        const { from, to } = selection;
        const isSame = oldState && oldState.doc.eq(doc) && oldState.selection.eq(selection);

        if (isSame) return;

        const shouldShow = this.shouldShow?.({
            editor: this.editor,
            view,
            state,
            oldState,
        });

        if (!shouldShow) return this.hide();

        // The dimension from posToDOMRect are relative to the window.
        // But since bard is inside a @container which creates a new stacking
        // context, we need the dimensions relative to the bard editor.
        const { top: selectionTop } = posToDOMRect(view, from, to);
        const { top: editorTop } = this.editor.options.element.getBoundingClientRect();
        this.vm.y = Math.round(selectionTop - editorTop);

        this.show();
    }

    show() {
        this.vm.show = true;
    }

    hide() {
        this.vm.show = false;
    }

    destroy() {
        this.editor.off('focus', this.focusHandler);
    }
}

export const FloatingMenuPlugin = (options) => {
    return new Plugin({
        key: new PluginKey(options.pluginKey),
        view: (view) => new FloatingMenuView({ view, ...options }),
    });
};
