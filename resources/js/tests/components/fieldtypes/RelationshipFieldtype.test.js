import { describe, expect, test } from 'vitest';
import RelationshipFieldtype from '@/components/fieldtypes/relationship/RelationshipFieldtype.vue';

const maxItems = (context) => RelationshipFieldtype.computed.maxItems.call({ config: {}, ...context });

describe('maxItems', () => {
    test('uses the configured max items', () => {
        expect(maxItems({ config: { max_items: 3 } })).toBe(3);
    });

    test('is unlimited without configured max items', () => {
        expect(maxItems({ handle: 'default' })).toBe(Infinity);
    });

    test('mirrors the max items being edited for the default config field', () => {
        const publishContainer = { asConfig: true, values: { max_items: '1' } };

        expect(maxItems({ handle: 'default', publishContainer })).toBe(1);
    });

    test('is unlimited when the max items being edited is cleared', () => {
        const publishContainer = { asConfig: true, values: { max_items: '' } };

        expect(maxItems({ handle: 'default', publishContainer })).toBe(Infinity);
    });

    test('ignores the max items being edited for other config fields', () => {
        const publishContainer = { asConfig: true, values: { max_items: '1' } };

        expect(maxItems({ handle: 'other', publishContainer })).toBe(Infinity);
    });
});
