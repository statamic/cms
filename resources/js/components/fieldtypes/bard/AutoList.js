/**
 * Adapted from medium-editor-autolist
 * https://github.com/varun-raj/medium-editor-autolist/blob/master/dist/autolist.js
 */
export default MediumEditor.Extension.extend({
    name: 'autolist',
    init: function () {
        this.subscribe('editableInput', this.onInput.bind(this));
    },
    onInput: function (keyPressEvent) {
        if (! this.base.getFocusedElement()) return;

        var list_start = this.base.getSelectedParentElement().textContent;
        if (/1\.\s/.test(list_start)) {
            this.base.execAction('delete');
            this.base.execAction('delete');
            this.base.execAction('delete');
            this.base.execAction('insertorderedlist');
        }
        else if (/[\*\-]\s/.test(list_start)) {
            this.base.execAction('delete');
            this.base.execAction('delete');
            this.base.execAction('insertunorderedlist');
        }
    }
});
