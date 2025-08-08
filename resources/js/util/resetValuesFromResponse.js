import Values from '@statamic/components/publish/Values.js';

export default function resetValuesFromResponse(responseValues, container) {
    const existingValues = container.values;

    if (!responseValues) return existingValues;

    let preserveFields = ['id'].concat(container.revealerFields);
    let originalValues = new Values(existingValues);
    let newValues = new Values(responseValues);

    newValues.mergeDottedKeys(preserveFields, originalValues);

    return newValues.all();
}
