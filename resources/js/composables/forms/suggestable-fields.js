export function expandImportedFields(fields, fieldsets) {
    const all = Object.values(fieldsets ?? {});

    const expand = (fields, prefix, seen) =>
        fields.flatMap((field) => {
            if (field.type !== 'import') return [prefix ? { ...field, handle: prefix + field.handle } : field];

            if (seen.includes(field.fieldset)) return [];

            const fieldset = all.find((fieldset) => fieldset.handle === field.fieldset);

            return expand(fieldset?.fields ?? [], prefix + (field.prefix ?? ''), [...seen, field.fieldset]);
        });

    return expand(fields, '', []);
}
