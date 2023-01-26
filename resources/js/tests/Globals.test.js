import { tidy_url } from '../bootstrap/globals'
import { data_get } from '../bootstrap/globals'

test('it tidies urls', () => {
    expect(tidy_url('foo/bar')).toBe('foo/bar');
    expect(tidy_url('foo//bar')).toBe('foo/bar');
    expect(tidy_url('foo///bar')).toBe('foo/bar');
    expect(tidy_url('foo////bar')).toBe('foo/bar');
    expect(tidy_url('http://foo//bar')).toBe('http://foo/bar');
    expect(tidy_url('https://foo//bar')).toBe('https://foo/bar');
    expect(tidy_url('notaprotocol://foo//bar')).toBe('notaprotocol://foo/bar');
});

test('it_can_get_value_from_data_using_dotted_path', () => {
    let data = {
        foo: 'alpha',
        nested: {
            foo: 'beta',
            array_value: [
                {foo: 'charlie'},
                {foo: 'delta'},
            ],
        },
    };

    expect(data_get(data, 'foo')).toStrictEqual('alpha');
    expect(data_get(data, 'nested.foo')).toStrictEqual('beta');
    expect(data_get(data, 'nested.array_value')).toStrictEqual([{foo: 'charlie'}, {foo: 'delta'}]);
    expect(data_get(data, 'nested.array_value.0.foo')).toStrictEqual('charlie');
    expect(data_get(data, 'nested.array_value.1.foo')).toStrictEqual('delta');
});
