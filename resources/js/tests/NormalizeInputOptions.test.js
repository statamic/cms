import { it, expect } from 'vitest';
import hasInputOptions from '../components/fieldtypes/HasInputOptions';
const normalizeInputOptions = hasInputOptions.methods.normalizeInputOptions;

const config = {
    translations: {
        '*.One': 'Uno',
    },
};

window.Statamic = {
    $config: {
        get: (key) => config[key],
    },
};

it('normalizes input options with simple array', () => {
    expect(normalizeInputOptions(['one', 'two'])).toEqual([
        { value: 'one', label: 'one' },
        { value: 'two', label: 'two' },
    ]);

    expect(normalizeInputOptions(['One', 'Two'])).toEqual([
        { value: 'One', label: 'Uno' },
        { value: 'Two', label: 'Two' },
    ]);
});

it('normalizes input options with object', () => {
    expect(
        normalizeInputOptions({
            one: 'One',
            two: 'Two',
        }),
    ).toEqual([
        { value: 'one', label: 'Uno' },
        { value: 'two', label: 'Two' },
    ]);
});

it('normalizes input options with array of objects with value label keys', () => {
    expect(
        normalizeInputOptions([
            { value: 'one', label: 'One' },
            { value: 'two', label: 'Two' },
        ]),
    ).toEqual([
        { value: 'one', label: 'Uno' },
        { value: 'two', label: 'Two' },
    ]);
});

it('normalizes input options with array of objects with key value keys', () => {
    expect(
        normalizeInputOptions([
            { key: 'one', value: 'One' },
            { key: 'two', value: 'Two' },
        ]),
    ).toEqual([
        { value: 'one', label: 'Uno' },
        { value: 'two', label: 'Two' },
    ]);
});
