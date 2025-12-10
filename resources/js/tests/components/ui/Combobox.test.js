import { expect, test, beforeEach, vi, describe } from 'vitest';
import { mount } from '@vue/test-utils';
import { Combobox } from '@/components/ui';

// Mock the ComboboxVirtualizer to render options without virtualization.
// This is necessary because virtualization relies on browser APIs which aren't available in our tests.
vi.mock('reka-ui', async () => {
    const actual = await vi.importActual('reka-ui');
    return {
        ...actual,
        ComboboxVirtualizer: {
            name: 'ComboboxVirtualizer',
            props: ['options', 'overscan', 'estimateSize', 'textContent'],
            setup(props, { slots }) {
                // Instead of virtualization, render all items directly
                return () => {
                    const items = props.options.map((option, index) => {
                        return slots.default({
                            option,
                            virtualItem: { index, key: index, start: index * 37 },
                            virtualizer: { scrollToIndex: () => {} }
                        });
                    });
                    return items;
                };
            }
        }
    };
});

// Mock the Scrollbar component since it relies on DOM APIs not available in tests
vi.mock('@ui/Combobox/Scrollbar.vue', () => ({
    default: {
        name: 'Scrollbar',
        props: ['viewport'],
        setup() {
            return {
                update: vi.fn()
            };
        },
        template: '<div />'
    }
}));

beforeEach(() => {
    Element.prototype.scrollIntoView = vi.fn();

    global.__ = (key) => key;

    global.CSS = {
        escape: (str) => str.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, '\\$&')
    };

    // Mock Canvas API for text measurement (needed for Combobox width calculation)
    HTMLCanvasElement.prototype.getContext = vi.fn(() => ({
        font: '',
        measureText: vi.fn((text) => ({
            width: text.length * 8 // Rough estimate: 8px per character
        }))
    }));

    document.body.innerHTML = '';
});

test('can select option', async () => {
    const wrapper = mount(Combobox, {
        props: {
            options: [
                { label: 'Jack', value: 'jack' },
                { label: 'Jason', value: 'jason' },
                { label: 'Jesse', value: 'jesse' },
                { label: 'Joshua', value: 'joshua' },
                { label: 'Juncan', value: 'juncan' },
                { label: 'Jay', value: 'jay' },
            ],
        },
    });

    const trigger = wrapper.find('[data-ui-combobox-trigger]');
    await trigger.trigger('click');

    // The option dropdown is rendered in a portal, so we need to find it in the document instead.
    await document.querySelector('[data-ui-combobox-item="jason"]').click();

    expect(wrapper.emitted('update:modelValue')[0]).toEqual(['jason']);
    await wrapper.setProps({ modelValue: 'jason' });

    expect(trigger.find('button').text()).toBe('Jason');
});

test('dropdown closes on selection', async () => {
    const wrapper = mount(Combobox, {
        props: {
            multiple: true,
            closeOnSelect: true,
            options: [
                { label: 'Jack', value: 'jack' },
                { label: 'Jason', value: 'jason' },
                { label: 'Jesse', value: 'jesse' },
                { label: 'Joshua', value: 'joshua' },
                { label: 'Juncan', value: 'juncan' },
                { label: 'Jay', value: 'jay' },
            ],
        },
    });

    await wrapper.find('[data-ui-combobox-trigger]').trigger('click');

    await document.querySelector('[data-ui-combobox-item="jason"]').click();
    expect(wrapper.emitted('update:modelValue')[0]).toEqual([['jason']]);
    await wrapper.setProps({ modelValue: ['jason'] });

    expect(wrapper.vm.dropdownOpen).toBeFalsy();

    expect(wrapper.find('[data-ui-combobox-selected-options]').text()).toContain('Jason');
});

test('can clear selected option', async () => {
    const wrapper = mount(Combobox, {
        props: {
            clearable: true,
            modelValue: 'juncan',
            options: [
                { label: 'Jack', value: 'jack' },
                { label: 'Jason', value: 'jason' },
                { label: 'Jesse', value: 'jesse' },
                { label: 'Joshua', value: 'joshua' },
                { label: 'Juncan', value: 'juncan' },
                { label: 'Jay', value: 'jay' },
            ],
        },
    });

    await wrapper.find('[data-ui-combobox-clear-button]').trigger('click');

    expect(wrapper.vm.searchQuery).toBe('');
    expect(wrapper.emitted('update:modelValue')[0]).toEqual([null]);
});

test('can use different optionLabel and optionValue keys', async () => {
    const wrapper = mount(Combobox, {
        props: {
            optionLabel: 'title',
            optionValue: 'id',
            options: [
                { title: 'Jack', id: 'jack' },
                { title: 'Jason', id: 'jason' },
                { title: 'Jesse', id: 'jesse' },
                { title: 'Joshua', id: 'joshua' },
                { title: 'Juncan', id: 'juncan' },
                { title: 'Jay', id: 'jay' },
            ],
        },
    });

    const trigger = wrapper.find('[data-ui-combobox-trigger]');
    await trigger.trigger('click');

    // The option dropdown is rendered in a portal, so we need to find it in the document instead.
    await document.querySelector('[data-ui-combobox-item="jason"]').click();

    expect(wrapper.emitted('update:modelValue')[0]).toEqual(['jason']);
    await wrapper.setProps({ modelValue: 'jason' });

    expect(trigger.find('button').text()).toBe('Jason');
});

describe('multiple options', () => {
    test('can select multiple options', async () => {
        const wrapper = mount(Combobox, {
            props: {
                multiple: true,
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                    { label: 'Joshua', value: 'joshua' },
                    { label: 'Juncan', value: 'juncan' },
                    { label: 'Jay', value: 'jay' },
                ],
            },
        });

        await wrapper.find('[data-ui-combobox-trigger]').trigger('click');

        await document.querySelector('[data-ui-combobox-item="jason"]').click();
        expect(wrapper.emitted('update:modelValue')[0]).toEqual([['jason']]);
        await wrapper.setProps({ modelValue: ['jason'] });

        await document.querySelector('[data-ui-combobox-item="jesse"]').click();
        expect(wrapper.emitted('update:modelValue')[1]).toEqual([['jason', 'jesse']]);
        await wrapper.setProps({ modelValue: ['jason', 'jesse'] });

        await document.querySelector('[data-ui-combobox-item="juncan"]').click();
        expect(wrapper.emitted('update:modelValue')[2]).toEqual([['jason', 'jesse', 'juncan']]);
        await wrapper.setProps({ modelValue: ['jason', 'jesse', 'juncan'] });

        expect(wrapper.find('[data-ui-combobox-selected-options]').text()).toContain('Jason');
        expect(wrapper.find('[data-ui-combobox-selected-options]').text()).toContain('Jesse');
        expect(wrapper.find('[data-ui-combobox-selected-options]').text()).toContain('Juncan');
    });

    test('cant select more than the allowed number of options', async () => {
        const wrapper = mount(Combobox, {
            props: {
                multiple: true,
                maxSelections: 2,
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                    { label: 'Joshua', value: 'joshua' },
                    { label: 'Juncan', value: 'juncan' },
                    { label: 'Jay', value: 'jay' },
                ],
            },
        });

        await wrapper.find('[data-ui-combobox-trigger]').trigger('click');

        await document.querySelector('[data-ui-combobox-item="jason"]').click();
        expect(wrapper.emitted('update:modelValue')[0]).toEqual([['jason']]);
        await wrapper.setProps({ modelValue: ['jason'] });

        await document.querySelector('[data-ui-combobox-item="jesse"]').click();
        expect(wrapper.emitted('update:modelValue')[1]).toEqual([['jason', 'jesse']]);
        await wrapper.setProps({ modelValue: ['jason', 'jesse'] });

        await document.querySelector('[data-ui-combobox-item="juncan"]').click();
        expect(wrapper.emitted('update:modelValue')).toHaveLength(2); // No new event should be emitted

        expect(wrapper.find('[data-ui-combobox-selected-options]').text()).toContain('Jason');
        expect(wrapper.find('[data-ui-combobox-selected-options]').text()).toContain('Jesse');
        expect(wrapper.find('[data-ui-combobox-selected-options]').text()).not.toContain('Juncan');
    });

    test('can deselect options', async () => {
        const wrapper = mount(Combobox, {
            props: {
                multiple: true,
                modelValue: ['jason', 'jesse', 'juncan'],
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                    { label: 'Joshua', value: 'joshua' },
                    { label: 'Juncan', value: 'juncan' },
                    { label: 'Jay', value: 'jay' },
                ],
            },
        });

        wrapper.find('[data-ui-combobox-selected-options] :nth-child(2) button').trigger('click');

        expect(wrapper.emitted('update:modelValue')[0]).toEqual([['jason', 'juncan']]);
        await wrapper.setProps({ modelValue: ['jason', 'juncan'] });

        expect(wrapper.find('[data-ui-combobox-selected-options]').text()).toContain('Jason');
        expect(wrapper.find('[data-ui-combobox-selected-options]').text()).not.toContain('Jesse');
        expect(wrapper.find('[data-ui-combobox-selected-options]').text()).toContain('Juncan');
    });
});

describe('search', () => {
    test('can search options', async () => {
        const wrapper = mount(Combobox, {
            props: {
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                    { label: 'Joshua', value: 'joshua' },
                    { label: 'Juncan', value: 'juncan' },
                    { label: 'Jay', value: 'jay' },
                ],
            },
        });

        const trigger = wrapper.find('[data-ui-combobox-trigger]');
        await trigger.trigger('click');

        await trigger.find('input[type="search"]').setValue('jac');

        expect(wrapper.vm.filteredOptions).toEqual([
            { label: 'Jack', value: 'jack' },
        ]);
    });

    test('cant search options when searchable prop is false', async () => {
        const wrapper = mount(Combobox, {
            props: {
                searchable: false,
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                    { label: 'Joshua', value: 'joshua' },
                    { label: 'Juncan', value: 'juncan' },
                    { label: 'Jay', value: 'jay' },
                ],
            },
        });

        const trigger = wrapper.find('[data-ui-combobox-trigger]');
        await trigger.trigger('click');

        expect(trigger.find('input[type="search"]').exists()).toBeFalsy();
    });

    test("doesn't search when ignoreFilter prop is true", async () => {
        const wrapper = mount(Combobox, {
            props: {
                ignoreFilter: true,
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                    { label: 'Joshua', value: 'joshua' },
                    { label: 'Juncan', value: 'juncan' },
                    { label: 'Jay', value: 'jay' },
                ],
            },
        });

        const trigger = wrapper.find('[data-ui-combobox-trigger]');
        await trigger.trigger('click');

        await trigger.find('input[type="search"]').setValue('jac');

        expect(wrapper.emitted('search')[0][0]).toEqual('jac');
    });
});

describe('taggable', () => {
    test('can append options', async () => {
        const wrapper = mount(Combobox, {
            props: {
                multiple: true,
                taggable: true,
                modelValue: [],
            },
        });

        const trigger = wrapper.find('[data-ui-combobox-trigger]');
        await trigger.trigger('click');

        const searchInput = trigger.find('input[type="search"]');
        await searchInput.setValue('Jack');
        await searchInput.trigger('keydown.enter');

        expect(wrapper.emitted('update:modelValue')[0]).toEqual([['Jack']]);
    });

    test('can paste into search input', async () => {
        const wrapper = mount(Combobox, {
            props: {
                multiple: true,
                taggable: true,
                modelValue: [],
            },
        });

        const trigger = wrapper.find('[data-ui-combobox-trigger]');
        await trigger.trigger('click');

        const searchInput = trigger.find('input[type="search"]');

        await searchInput.trigger('paste', {
            clipboardData: {
                getData: () => 'Jack,Jason,Jesse',
                types: ['text/plain']
            }
        });

        expect(wrapper.emitted('update:modelValue')[0]).toEqual([['Jack', 'Jason', 'Jesse']]);
    });
});

describe('accessibility', () => {
    test('dropdown opens on space', async () => {
        const wrapper = mount(Combobox, {
            props: {
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                    { label: 'Joshua', value: 'joshua' },
                    { label: 'Juncan', value: 'juncan' },
                    { label: 'Jay', value: 'jay' },
                ],
            },
        });

        expect(wrapper.vm.dropdownOpen).toBeFalsy();

        await wrapper.find('[data-ui-combobox-trigger]').trigger('keydown.space');

        expect(wrapper.vm.dropdownOpen).toBeTruthy();
    });

    test('dropdown closes on escape', async () => {
        const wrapper = mount(Combobox, {
            props: {
                closeOnSelect: false,
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                    { label: 'Joshua', value: 'joshua' },
                    { label: 'Juncan', value: 'juncan' },
                    { label: 'Jay', value: 'jay' },
                ],
            },
        });

        const trigger = wrapper.find('[data-ui-combobox-trigger]');
        await trigger.trigger('click');

        expect(wrapper.vm.dropdownOpen).toBeTruthy();

        const options = document.querySelector('[data-ui-combobox-content]');

        options.dispatchEvent(new KeyboardEvent('keydown', {
            key: 'Escape',
            keyCode: 27,
            which: 27,
            bubbles: true,
            cancelable: true
        }));

        expect(wrapper.vm.dropdownOpen).toBeFalsy();
    });
});
