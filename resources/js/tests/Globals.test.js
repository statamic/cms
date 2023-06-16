import { tidy_url } from '../bootstrap/globals'
import { data_get } from '../bootstrap/globals'
import { data_set } from '../bootstrap/globals'

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

test('it_can_set_value_to_data_using_dotted_path', () => {
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

    data_set(data, 'foo', 'alpha_changed');
    data_set(data, 'bar', 'bar_added');
    data_set(data, 'nested.foo', 'beta_changed');
    data_set(data, 'nested.bar', 'bar_added');
    data_set(data, 'nested.array_value.0.foo', 'delta_changed');
    data_set(data, 'nested.array_value.1', 'element_two_is_now_string');
    data_set(data, 'nested.array_value.2', 'element_added');
    data_set(data, 'deep.new.object', 'deep_new_object_value_added');
    data_set(data, 'deep.new.array.0', 'deep_new_array_element_added');

    let expected = {
        foo: 'alpha_changed',
        bar: 'bar_added',
        nested: {
            foo: 'beta_changed',
            bar: 'bar_added',
            array_value: [
                {foo: 'delta_changed'},
                'element_two_is_now_string',
                'element_added',
            ],
        },
        deep: {
            new: {
                object: 'deep_new_object_value_added',
                array: [
                    'deep_new_array_element_added',
                ],
            },
        },
    };

    expect(data).toStrictEqual(expected);
});
