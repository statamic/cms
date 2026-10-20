import { afterEach, expect, test } from 'vitest';
import { mount } from '@vue/test-utils';
import Textarea from '@/components/ui/Textarea.vue';

let wrapper;
afterEach(() => wrapper?.unmount());

const text = 'The quick brown fox jumps over the lazy dog. '.repeat(20);

function render(modelValue) {
    wrapper = mount(Textarea, { attachTo: document.body, props: { elastic: true, modelValue } });
    return wrapper.find('textarea').element;
}

test('an elastic textarea grows when its value is changed programmatically', async () => {
    const textarea = render('');

    await wrapper.setProps({ modelValue: text });

    expect(textarea.scrollHeight).toBeLessThanOrEqual(textarea.clientHeight);
});

test('an elastic textarea shrinks when its value is cleared programmatically', async () => {
    const textarea = render(text);
    const initialHeight = textarea.offsetHeight;

    await wrapper.setProps({ modelValue: '' });

    expect(textarea.offsetHeight).toBeLessThan(initialHeight);
});
