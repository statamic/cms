import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { computed } from 'vue';
import * as Globals from '@/bootstrap/globals';
import TableFieldtype from '@/components/fieldtypes/TableFieldtype.vue';
import { containerContextKey } from '@/components/ui/Publish/Container.vue';
import { Input, Button } from '@/components/ui';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));

test('value cells get content direction while chrome does not', () => {
    document.documentElement.setAttribute('dir', 'ltr');

    const wrapper = mount(TableFieldtype, {
        props: {
            value: [{ cells: ['hello', 'world'] }],
            handle: 'table',
        },
        components: {
            'ui-input': Input,
            'ui-button': Button,
        },
        global: {
            provide: {
                [containerContextKey]: {
                    direction: computed(() => 'rtl'),
                },
            },
        },
    });

    const input = wrapper.find('input');
    expect(input.attributes('dir')).toBe('rtl');

    const deleteColumnButton = wrapper.find('[aria-label="Delete Column"]');
    expect(deleteColumnButton.attributes('dir')).toBeUndefined();
});
