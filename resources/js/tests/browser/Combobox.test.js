import { afterEach, expect, test, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { Combobox } from '@/components/ui';
import { __, __n } from '@/bootstrap/globals';

Object.assign(window, { __, __n });

const options = [{ label: '<span title="2:1"><svg viewBox="0 0 28 20"></svg>\n    <span>2:1</span></span>', value: '2/1' }];

let wrapper;
afterEach(() => wrapper?.unmount());

function render(props) {
    wrapper = mount(Combobox, {
        attachTo: document.body,
        props: { options, labelHtml: true, searchable: false, ...props },
        global: { mocks: { __ } },
    });
}

test('html option labels are plain text in the option title and typeahead value', async () => {
    render({ modelValue: null });

    await wrapper.get('[data-ui-combobox-trigger]').trigger('keydown', { key: 'Enter' });

    const option = await vi.waitFor(() => {
        const renderedOption = document.querySelector('[data-ui-combobox-item="2/1"]');
        expect(renderedOption).not.toBeNull();

        return renderedOption;
    });

    expect(option.getAttribute('title')).toBe('2:1');
    expect(option.getAttribute('textvalue')).toBe('2:1');
});

test('html option labels are plain text in the remove button label', () => {
    render({ modelValue: ['2/1'], multiple: true });

    expect(wrapper.get('[data-ui-combobox-selected-options] button').attributes('aria-label')).toBe('Remove 2:1');
});
