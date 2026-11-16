import { afterEach, expect, test } from 'vitest';
import { mount } from '@vue/test-utils';
import Textarea from '@/components/ui/Textarea.vue';
import wait from '@/util/wait.js';
import '../../../css/app.css';

let wrapper;
let host;
afterEach(() => {
    wrapper?.unmount();
    host?.remove();
});

const text = 'The quick brown fox jumps over the lazy dog. '.repeat(20);

function render(modelValue) {
    host = document.createElement('div');
    host.style.width = '600px';
    document.body.append(host);
    wrapper = mount(Textarea, { attachTo: host, props: { elastic: true, modelValue } });
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

test('an elastic textarea grows when it gets narrower', async () => {
    const textarea = render(text);
    await wait(50);

    host.style.width = '300px';
    await wait(100);

    expect(textarea.scrollHeight).toBeLessThanOrEqual(textarea.clientHeight);
});
