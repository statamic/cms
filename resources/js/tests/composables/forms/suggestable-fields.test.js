import { expect, test } from 'vitest';
import { expandImportedFields } from '@/composables/forms/suggestable-fields';

const fieldsets = {
    contact: {
        handle: 'contact',
        fields: [
            { handle: 'company', type: 'inline', fieldtype: 'text', config: { type: 'text', display: 'Company' } },
            { type: 'import', fieldset: 'address', prefix: 'home_' },
        ],
    },
    address: {
        handle: 'address',
        fields: [{ handle: 'street', type: 'inline', fieldtype: 'text', config: { type: 'text', display: 'Street' } }],
    },
    loop: {
        handle: 'loop',
        fields: [
            { handle: 'one', type: 'inline', fieldtype: 'text', config: { type: 'text' } },
            { type: 'import', fieldset: 'loop' },
        ],
    },
};

test('imports are replaced by their prefixed fieldset fields', () => {
    const fields = [
        { _id: 'a', handle: 'name', type: 'inline', fieldtype: 'text', config: { type: 'text' } },
        { _id: 'b', type: 'import', fieldset: 'contact', prefix: 'work_' },
    ];

    expect(expandImportedFields(fields, fieldsets).map((f) => f.handle)).toEqual(['name', 'work_company', 'work_home_street']);
    expect(expandImportedFields(fields, fieldsets)[1].config.display).toBe('Company');
});

test('imports without a prefix keep the fieldset handles', () => {
    const fields = [{ _id: 'b', type: 'import', fieldset: 'address' }];

    expect(expandImportedFields(fields, fieldsets).map((f) => f.handle)).toEqual(['street']);
});

test('missing and recursive fieldsets are skipped', () => {
    const fields = [
        { _id: 'a', type: 'import', fieldset: 'missing' },
        { _id: 'b', type: 'import', fieldset: 'loop' },
    ];

    expect(expandImportedFields(fields, fieldsets).map((f) => f.handle)).toEqual(['one']);
});

test('fieldsets can be given as an array', () => {
    const fields = [{ _id: 'b', type: 'import', fieldset: 'address', prefix: 'p_' }];

    expect(expandImportedFields(fields, Object.values(fieldsets)).map((f) => f.handle)).toEqual(['p_street']);
});
