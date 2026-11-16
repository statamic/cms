import { afterEach, expect, test, vi } from 'vitest';
import { defineComponent, nextTick, ref } from 'vue';
import { mount } from '@vue/test-utils';
import Reveal from '@/components/Reveal.js';

afterEach(() => {
    document.body.innerHTML = '';
});

test.each(['ref', 'getter'])('registers a delayed %s target and removes it on unmount', async (source) => {
    const reveal = new Reveal();
    const callback = vi.fn();
    const visible = ref(false);
    const wrapper = mount(defineComponent({
        setup() {
            const target = ref(null);
            reveal.use(source === 'ref' ? target : () => target.value, callback);

            return { target, visible };
        },
        template: '<section v-if="visible" ref="target"><input /></section>',
    }));

    visible.value = true;
    await nextTick();

    const target = wrapper.get('section').element;
    const input = wrapper.get('input').element;
    input.scrollIntoView = vi.fn();

    reveal.element(input);
    expect(callback).toHaveBeenCalledWith(target);
    await nextTick();

    wrapper.unmount();
    callback.mockClear();

    reveal.element(input);
    expect(callback).not.toHaveBeenCalled();
    await nextTick();
});

test('invalid focuses the first control inside the first errored field', async () => {
    document.body.innerHTML = `
        <div data-ui-field-has-errors id="field-wrap">
            <input data-ui-control id="field_title" />
        </div>
        <div data-ui-field-has-errors>
            <input data-ui-control id="field_slug" />
        </div>
    `;

    const field = document.getElementById('field-wrap');
    const first = document.getElementById('field_title');
    const second = document.getElementById('field_slug');
    field.scrollIntoView = vi.fn();
    first.focus = vi.fn();
    second.focus = vi.fn();

    const reveal = new Reveal();
    reveal.invalid();

    await nextTick();
    await nextTick();

    expect(first.focus).toHaveBeenCalled();
    expect(second.focus).not.toHaveBeenCalled();
});

test('invalid focuses a slider thumb instead of the non-focusable root', async () => {
    document.body.innerHTML = `
        <div data-ui-field-has-errors id="field-wrap">
            <div data-ui-control>
                <span role="slider" tabindex="0" id="field_quality"></span>
            </div>
        </div>
    `;

    const field = document.getElementById('field-wrap');
    const thumb = document.getElementById('field_quality');
    field.scrollIntoView = vi.fn();
    thumb.focus = vi.fn();

    const reveal = new Reveal();
    reveal.invalid();

    await nextTick();
    await nextTick();

    expect(thumb.focus).toHaveBeenCalled();
});

test('invalid skips a disabled combobox trigger', async () => {
    document.body.innerHTML = `
        <div data-ui-field-has-errors id="field-wrap">
            <div role="combobox" tabindex="-1" data-disabled></div>
            <input id="field_fallback" />
        </div>
    `;

    const field = document.getElementById('field-wrap');
    const fallback = document.getElementById('field_fallback');
    field.scrollIntoView = vi.fn();
    fallback.focus = vi.fn();

    const reveal = new Reveal();
    reveal.invalid();

    await nextTick();
    await nextTick();

    expect(fallback.focus).toHaveBeenCalled();
});
