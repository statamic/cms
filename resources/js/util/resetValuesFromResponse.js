import Values from '@statamic/components/publish/Values.js';

export default function resetValuesFromResponse(responseValues, store) {
    const existingValues = store.values;

    if (!responseValues) return existingValues;

    let preserveFields = ['id'].concat(store.revealerFields);
    let originalValues = new Values(existingValues, store.jsonSubmittingFields);
    let newValues = new Values(responseValues, store.jsonSubmittingFields);

    newValues.mergeDottedKeys(preserveFields, originalValues);

    return newValues.all();
}
