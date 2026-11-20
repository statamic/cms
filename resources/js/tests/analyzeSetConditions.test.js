import { describe, expect, test } from 'vitest';
import analyzeSetConditions from '@/components/field-conditions/analyzeSetConditions.js';

function analyze(fields) {
    return analyzeSetConditions({ handle: 'main', fields });
}

describe('needsRootValues', () => {
    test('is false when there are no conditions at all', () => {
        expect(analyze([{ handle: 'one', type: 'text' }]).needsRootValues).toBe(false);
    });

    test('is false when conditions only point at the set’s own fields', () => {
        const fields = [
            { handle: 'one', type: 'text' },
            { handle: 'two', type: 'text', if: { one: 'equals yes' } },
            { handle: 'three', type: 'text', unless_any: { one: 'not yes', two: 'contains a' } },
        ];

        expect(analyze(fields).needsRootValues).toBe(false);
    });

    test('is false for a dotted path, which data_get can only resolve within the set', () => {
        expect(analyze([{ handle: 'one', type: 'text', if: { 'group.nested': 'equals yes' } }]).needsRootValues).toBe(
            false,
        );
    });

    test.each(['$root.driver', 'root.driver', '$parent.driver', '$parent.$parent.driver'])(
        'is true for a condition on %s',
        (lhs) => {
            expect(analyze([{ handle: 'one', type: 'text', if: { [lhs]: 'equals yes' } }]).needsRootValues).toBe(true);
        },
    );

    test('is true for a custom condition with a target, which is handed the root values', () => {
        expect(analyze([{ handle: 'one', type: 'text', if: { two: 'custom SomeCondition' } }]).needsRootValues).toBe(
            true,
        );
    });

    test('is true for a custom condition without a target', () => {
        expect(analyze([{ handle: 'one', type: 'text', if: 'custom SomeCondition' }]).needsRootValues).toBe(true);
    });

    test('is true for every condition key, not just `if`', () => {
        const keys = ['if', 'if_any', 'show_when', 'show_when_any', 'unless', 'unless_any', 'hide_when', 'hide_when_any'];

        keys.forEach((key) => {
            expect(analyze([{ handle: 'one', type: 'text', [key]: { '$root.driver': 'equals yes' } }]).needsRootValues)
                .toBe(true);
        });
    });

    test('is true when a later key is the external one', () => {
        const field = { handle: 'one', type: 'text', if: { two: 'equals yes' }, unless: { '$root.driver': 'equals no' } };

        expect(analyze([field]).needsRootValues).toBe(true);
    });

    // Anything we don't recognise has to take the slow branch. Guessing "narrow" on a
    // condition syntax we can't read is what drops values from the save payload.
    test.each([
        ['a condition block that isn’t an object', { if: ['one equals yes'] }],
        ['a right hand side that isn’t a scalar', { if: { one: { equals: 'yes' } } }],
    ])('is true for %s', (_, config) => {
        expect(analyze([{ handle: 'one', type: 'text', ...config }]).needsRootValues).toBe(true);
    });

    test('is true when the set’s fields aren’t readable', () => {
        expect(analyze('nope').needsRootValues).toBe(true);
        expect(analyzeSetConditions(undefined).needsRootValues).toBe(true);
    });
});

describe('canDeferMount', () => {
    test('is true for a flat set', () => {
        const fields = [
            { handle: 'one', type: 'text' },
            { handle: 'two', type: 'text', if: { '$root.driver': 'equals yes' } },
        ];

        expect(analyze(fields).canDeferMount).toBe(true);
    });

    test('is true when nested fields have no conditions', () => {
        const fields = [{ handle: 'grid', type: 'grid', fields: [{ handle: 'row', type: 'text' }] }];

        expect(analyze(fields).canDeferMount).toBe(true);
    });

    test('is false when a nested field has a condition', () => {
        const fields = [
            {
                handle: 'grid',
                type: 'grid',
                fields: [
                    { handle: 'row', type: 'text' },
                    { handle: 'row_two', type: 'text', if: { row: 'equals yes' } },
                ],
            },
        ];

        expect(analyze(fields).canDeferMount).toBe(false);
    });

    test('is false when a condition is inside a nested replicator set', () => {
        const fields = [
            {
                handle: 'rep',
                type: 'replicator',
                sets: [
                    {
                        handle: 'group_one',
                        sets: [
                            {
                                handle: 'nested',
                                fields: [{ handle: 'inner', type: 'text', if: { other: 'equals yes' } }],
                            },
                        ],
                    },
                ],
            },
        ];

        expect(analyze(fields).canDeferMount).toBe(false);
    });

    test('is false when the set contains a revealer, which only registers itself on mount', () => {
        expect(analyze([{ handle: 'toggle', type: 'revealer' }]).canDeferMount).toBe(false);
    });

    test('is false when a nested field is a revealer', () => {
        const fields = [{ handle: 'group', type: 'group', fields: [{ handle: 'toggle', type: 'revealer' }] }];

        expect(analyze(fields).canDeferMount).toBe(false);
    });

    test('is false when nested fields aren’t readable', () => {
        expect(analyze([{ handle: 'grid', type: 'grid', fields: 'nope' }]).canDeferMount).toBe(false);
        expect(analyze([{ handle: 'grid', type: 'grid', fields: ['nope'] }]).canDeferMount).toBe(false);
    });
});

test('the result is memoised per config object', () => {
    const config = { handle: 'main', fields: [{ handle: 'one', type: 'text' }] };

    expect(analyzeSetConditions(config)).toBe(analyzeSetConditions(config));
});
