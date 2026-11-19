import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { h, ref } from 'vue';
import SelectFieldtype from '@/components/fieldtypes/SelectFieldtype.vue';

window.__ = (key) => key;

test('replicator preview is exposed to the parent field', () => {
    const fieldtype = ref();

    mount(() =>
        h(SelectFieldtype, {
            ref: fieldtype,
            value: 'two',
            handle: 'select',
            config: { options: { one: 'One', two: 'Two' } },
            showFieldPreviews: true,
        }),
    );

    expect(fieldtype.value.replicatorPreview).toBe('Two');
});
