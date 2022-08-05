import resolvePath from '../components/publish/FieldPathResolver.js';

test('it resolves paths without placeholders', () => {
    let values = {
        text: 'dont care'
    };

    expect(resolvePath('text', values)).toEqual('text');
    expect(resolvePath('foo.bar', values)).toEqual('foo.bar');
});

test('it resolves replicator/grid paths', () => {
    let values = {
        text: 'dont care',
        replicator_field: [
            {
                _id: 'a',
                text: 'alfa',
            },
            {
                _id: 'b',
                text: 'bravo',
            }
        ]
    };

    let resolved = resolvePath('replicator_field.{replicator:b}.text', values);

    expect(resolved).toEqual('replicator_field.1.text');
});

test('it resolves bard paths', () => {
    let values = {
        text: 'dont care',
        bard_field: [
            {
                type: 'paragraph',
                content: [{ type: 'text', text: 'some text' }]
            },
            {
                type: 'set',
                attrs: {
                    id: 'a',
                    values: {
                        text: 'alfa',
                    }
                }
            },
            {
                type: 'paragraph',
                content: [{ type: 'text', text: 'some more text' }]
            },
            {
                type: 'set',
                attrs: {
                    id: 'b',
                    values: {
                        text: 'bravo',
                    }
                }
            }
        ]
    };

    expect(resolvePath('bard_field.{bard:a}.text', values)).toEqual('bard_field.1.attrs.values.text');
    expect(resolvePath('bard_field.{bard:b}.text', values)).toEqual('bard_field.3.attrs.values.text');
});

test('it resolves paths recursively', () => {
    let values = {
        text: 'dont care',
        replicator: [
            {
                _id: 'a',
                text: 'alfa',
                grid: [
                    {
                        _id: 'd',
                        text: 'delta',
                        bard: [
                            { type: 'paragraph', content: [], },
                        ]
                    },
                    {
                        _id: 'g',
                        text: 'golf',
                        bard: [
                            { type: 'paragraph', content: [], },
                            { type: 'set', attrs: { id: 'y', values: {text: 'foo'} } },
                            { type: 'paragraph', content: [], },
                            { type: 'set', attrs: { id: 'z', values: {text: 'bar'} } }
                        ]
                    }
                ]
            },
            {
                _id: 'b',
                text: 'bravo',
                grid: [
                    {
                        _id: 'e',
                        text: 'echo',
                    },
                    {
                        _id: 'f',
                        text: 'foxtrot',
                    }
                ]
            }
        ]
    };

    expect(resolvePath('replicator.{replicator:a}.grid.{grid:g}.bard.{bard:z}.text', values))
        .toEqual('replicator.0.grid.1.bard.3.attrs.values.text');

    expect(resolvePath('replicator.{replicator:b}.grid.{grid:f}.text', values))
        .toEqual('replicator.1.grid.1.text');
});
