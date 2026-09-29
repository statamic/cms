import { flushPromises, mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { ref } from 'vue';
import SlugFieldtype from '@/components/fieldtypes/SlugFieldtype.vue';
import Slugify from '@/components/slugs/Slugify.vue';
import { publishContextKey } from '@/components/ui';

window.__ = (key) => key;
window.Statamic = { $config: { get: () => [{ handle: 'en', lang: 'en', direction: 'ltr' }] } };

function mountFieldtype(value, provide = {}) {
    return mount(SlugFieldtype, {
        props: {
            handle: 'slug',
            value,
            meta: null,
            config: { generate: true, from: 'title', async: false },
        },
        global: {
            components: { Slugify },
            provide: {
                [publishContextKey]: {
                    values: ref({ title: 'Michael Aerni', slug: value }),
                    site: ref('en'),
                },
                ...provide,
            },
            mocks: {
                $slug: {
                    in: () => ({ separatedBy: () => ({ create: (str) => str.toLowerCase().replace(/\s/g, '-') }) }),
                },
                $events: { $on: () => {}, $off: () => {} },
            },
        },
    });
}

test('the slug is flagged as auto generated until the user edits it', async () => {
    const wrapper = mountFieldtype(null);

    expect(wrapper.emitted('update:meta').at(-1)).toEqual([{ auto: true }]);

    await wrapper.find('input').setValue('something-else');

    expect(wrapper.emitted('update:meta').at(-1)).toEqual([{ auto: false }]);
});

test('the slug is not flagged as auto generated when the entry already has one', () => {
    const wrapper = mountFieldtype('michael-aerni');

    expect(wrapper.emitted('update:meta').at(-1)).toEqual([{ auto: false }]);
});

test('the slug is generated from the entry slug source when there is one', async () => {
    const source = ref('');
    const wrapper = mountFieldtype(null, { entrySlugSource: () => source.value });

    expect(wrapper.vm.slug).toBeNull();

    source.value = '56 Michael Aerni';
    await flushPromises();

    expect(wrapper.vm.slug).toBe('56-michael-aerni');
    expect(wrapper.emitted('update:meta').at(-1)).toEqual([{ auto: true }]);
});
