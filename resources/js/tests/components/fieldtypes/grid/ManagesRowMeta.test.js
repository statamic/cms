import { expect, test } from 'vitest';
import ManagesRowMeta from '@/components/fieldtypes/grid/ManagesRowMeta';

const { duplicateValues } = ManagesRowMeta.methods;

test('it leaves values without ids alone', () => {
    const { values } = duplicateValues({ type: 'text', text: 'Hello', tags: ['one', 'two'], link: null }, {});

    expect(values).toEqual({ type: 'text', text: 'Hello', tags: ['one', 'two'], link: null });
});

test('it gives grid rows nested in a set new ids', () => {
    const { values, meta } = duplicateValues(
        {
            _id: 'set-1',
            type: 'my_set',
            grid: [
                { _id: 'row-1', text: 'One' },
                { _id: 'row-2', text: 'Two' },
            ],
        },
        { grid: { existing: { 'row-1': { text: {} }, 'row-2': { text: {} } } } },
    );

    const [first, second] = values.grid.map((row) => row._id);

    expect(values._id).not.toBe('set-1');
    expect(first).not.toBe('row-1');
    expect(second).not.toBe('row-2');
    expect(first).not.toBe(second);
    expect(Object.keys(meta.grid.existing)).toEqual([first, second]);
});

test('it gives bard sets nested in a set new ids', () => {
    const { values, meta } = duplicateValues(
        {
            _id: 'set-1',
            type: 'my_set',
            bard: [
                { type: 'paragraph', content: [{ type: 'text', text: 'Hello' }] },
                { type: 'set', attrs: { id: 'bard-1', enabled: true, values: { type: 'quote' } } },
            ],
        },
        { bard: { existing: { 'bard-1': { quote: {} } }, collapsed: ['bard-1'] } },
    );

    const nestedId = values.bard[1].attrs.id;

    expect(nestedId).not.toBe('bard-1');
    expect(values.bard[0]).toEqual({ type: 'paragraph', content: [{ type: 'text', text: 'Hello' }] });
    expect(Object.keys(meta.bard.existing)).toEqual([nestedId]);
    expect(meta.bard.collapsed).toEqual([nestedId]);
});

test('it regenerates ids at every level of nesting', () => {
    const { values } = duplicateValues({ _id: 'set-1', replicator: [{ _id: 'set-2', replicator: [{ _id: 'set-3' }] }] }, {});

    expect(values._id).not.toBe('set-1');
    expect(values.replicator[0]._id).not.toBe('set-2');
    expect(values.replicator[0].replicator[0]._id).not.toBe('set-3');
});

test('it does not modify the values or meta it was given', () => {
    const values = { _id: 'set-1', grid: [{ _id: 'row-1' }] };
    const meta = { grid: { existing: { 'row-1': {} } } };

    duplicateValues(values, meta);

    expect(values).toEqual({ _id: 'set-1', grid: [{ _id: 'row-1' }] });
    expect(meta).toEqual({ grid: { existing: { 'row-1': {} } } });
});
