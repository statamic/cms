import { isPlainObject } from 'lodash-es';
import ShowField from '@/components/field-conditions/ShowField.js';
import { KEYS } from '@/components/field-conditions/Constants.js';

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

            const dottedKey = `${prefix}.${field.handle}`;

            if (!conditionsResolveFromPath(field)) {
                keepValue(container, dottedKey);
                return;
            }

            showField.showField(field, dottedKey);
        });
    });
}

// The field path isn't only a key to write bookkeeping under, it's an input to the
// conditions themselves: `$parent.` is resolved by walking up it. A Bard set's field path
// carries two more segments than a Replicator's — `bard.0.attrs.values.foo` against
// `rep.0.foo` — so the walk lands on a path that doesn't exist and the condition can never
// pass. That's a pre-existing bug, and a mounted set gets the same wrong answer; the
// difference is that a mounted set is on screen, whereas here the answer would be applied
// to a field nobody has ever looked at, and would drop it from every save.
//
// So don't answer at all. Keeping a field the blueprint would have dropped is noise;
// dropping one it would have kept is lost content.
//
// Nothing else about a set's conditions reads the field path. `$root.` reads the
// container's values and a bare or dotted handle reads the set's own, and both are the
// same values a mounted set would be handed.
const PARENT_RE = /^\$parent\./;

function conditionsResolveFromPath(field) {
    return KEYS.filter((key) => field[key]).every((key) => resolvableConditions(field[key]));
}

function resolvableConditions(conditions) {
    // A bare string is a custom condition without a target. Its callback is handed the
    // same field path either way, so there's nothing here a mounted set resolves better.
    if (typeof conditions === 'string') return true;

    if (!isPlainObject(conditions)) return false;

    return Object.keys(conditions).every((lhs) => !PARENT_RE.test(lhs));
}

function keepValue(container, dottedKey) {
    const current = container.hiddenFields.value[dottedKey];

    // Writing unconditionally would retrigger the watcher this runs from.
    if (current && current.hidden === false && current.omitValue === false) return;

    container.setHiddenField({ dottedKey, hidden: false, omitValue: false });
}

function fieldList(fields) {
    if (Array.isArray(fields)) return fields;
    if (isPlainObject(fields)) return Object.values(fields);

    return [];
}
