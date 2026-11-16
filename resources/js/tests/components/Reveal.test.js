import { afterEach, expect, test, vi } from 'vitest';
import { nextTick } from 'vue';
import Reveal from '@/components/Reveal.js';

afterEach(() => {
    document.body.innerHTML = '';
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
