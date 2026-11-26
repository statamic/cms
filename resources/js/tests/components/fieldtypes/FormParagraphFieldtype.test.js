import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import FormParagraphFieldtype from '@/components/fieldtypes/FormParagraphFieldtype.vue';

window.__ = (key) => key;

function mountField(config = {}) {
    return mount(FormParagraphFieldtype, {
        props: {
            value: null,
            handle: 'paragraph',
            config,
        },
    });
}

test('renders the paragraph text', () => {
    const wrapper = mountField({ text: 'Thanks for getting in touch.' });

    expect(wrapper.text()).toBe('Thanks for getting in touch.');
});

/** @see https://github.com/statamic/cms/issues/16071 */
test('renders a placeholder when the paragraph has no text', () => {
    const wrapper = mountField({ text: null });

    expect(wrapper.text()).toBe('Empty paragraph');
});
