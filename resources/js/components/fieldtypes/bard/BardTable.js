import { Table as TableBase } from '@tiptap/extension-table';
import { columnResizing, tableEditing } from '@tiptap/pm/tables';

/**
 * TipTap's Table extension only registers columnResizing when `resizable && editor.isEditable`,
 * so read-only Bard loses the tableWrapper node view and tables render as bare <table> nodes.
 * Mount columnResizing whenever `resizable` is true so DOM matches editable mode; resize
 * interaction is already inactive when the editor is not editable.
 */
export const BardTable = TableBase.extend({
    addProseMirrorPlugins() {
        const isResizable = this.options.resizable;

        return [
            ...(isResizable
                ? [
                      columnResizing({
                          handleWidth: this.options.handleWidth,
                          cellMinWidth: this.options.cellMinWidth,
                          defaultCellMinWidth: this.options.cellMinWidth,
                          View: this.options.View,
                          lastColumnResizable: this.options.lastColumnResizable,
                      }),
                  ]
                : []),
            tableEditing({
                allowTableNodeSelection: this.options.allowTableNodeSelection,
            }),
        ];
    },
});
