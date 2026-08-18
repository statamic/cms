import { isPlainObject } from 'lodash-es';
import ShowField from '@/components/field-conditions/ShowField.js';

// A Bard field that has never been scrolled into view has no editor, so it has no set
// node views either — there is no `bard/Set.vue` to evaluate the conditions of the fields
// inside its sets, headlessly or otherwise. Nothing writes their omitValue bookkeeping,
// and a save keeps fields the blueprint says are hidden.
//
// So evaluate them from the stored value instead. The paths have to match exactly what a
// mounted set would write, because a wrong key writes bookkeeping for a field that isn't
// the one we evaluated. A set's fields live at `<prefix>.<index>.attrs.values.<handle>`,
// where the index is the node's position in the document. Without an editor the stored
// value *is* the document — and it's the tree the container omits from — so the index is
// unambiguous.
//
// That last part is also why this must stop the moment an editor exists. From then on the
// editor's copy of the document is the authoritative one, the indexes are its, and the
// node views own the bookkeeping.
export default function evaluateBardSetConditions({ value, setConfigs, fieldPathPrefix, container }) {
    if (!Array.isArray(value) || !Array.isArray(setConfigs) || setConfigs.length === 0) return;

    value.forEach((node, index) => {
        if (!isPlainObject(node) || node.type !== 'set') return;

        const values = node.attrs?.values;
        if (!isPlainObject(values)) return;

        const config = setConfigs.find((set) => set?.handle === values.type);
        if (!isPlainObject(config)) return;

        const fields = fieldList(config.fields);
        if (fields.length === 0) return;

        const prefix = `${fieldPathPrefix}.${index}.attrs.values`;

        const showField = new ShowField(
            values,
            {},
            container.visibleValues.value,
            container.revealerValues.value,
            container.hiddenFields.value,
            container.setHiddenField,
            { container: container.container },
        );

        fields.forEach((field) => {
            if (!isPlainObject(field) || !field.handle) return;

            showField.showField(field, `${prefix}.${field.handle}`);
        });
    });
}

function fieldList(fields) {
    if (Array.isArray(fields)) return fields;
    if (isPlainObject(fields)) return Object.values(fields);

    return [];
}
