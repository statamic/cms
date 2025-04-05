import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import TextFieldtype from '@statamic/components/fieldtypes/TextFieldtype.vue';
import Input from '@statamic/components/ui/Input/Index.vue';

test('value can be updated', async () => {
    const wrapper = mount(TextFieldtype, {
        props: {
            value: null,
            handle: 'name',
        },
        components: {
            Input,
        },
    });

    const input = wrapper.find('input');
    expect(input.element.value).toBe('');
    expect(wrapper.emitted('update:value')).toBeUndefined();

    await input.setValue('John');
    expect(input.element.value).toBe('John');
    expect(wrapper.emitted('update:value')[0]).toEqual(['John']);
});
