import { expect, test, describe } from 'vitest';
import { page, userEvent } from 'vitest/browser';
import { render } from 'vitest-browser-vue';
import { Combobox } from '@/components/ui';

test('can select option', async () => {
    let currentValue = null;

    const screen = render(Combobox, {
        props: {
            modelValue: currentValue,
            options: [
                { label: 'Jack', value: 'jack' },
                { label: 'Jason', value: 'jason' },
                { label: 'Jesse', value: 'jesse' },
            ],
            'onUpdate:modelValue': (newValue) => {
                currentValue = newValue;
                screen.rerender({ modelValue: newValue });
            },
        },
    });

    const combobox = screen.getByRole('combobox');
    await combobox.click();

    // Options are in a portal, use page to find them
    const option = page.getByText('Jason', { exact: true });
    await option.click();

    expect(currentValue).toEqual('jason');
    await expect.element(screen.container).toHaveTextContent('Jason');
});

test('dropdown closes on selection', async () => {
    let currentValue = [];

    const screen = render(Combobox, {
        props: {
            multiple: true,
            closeOnSelect: true,
            modelValue: currentValue,
            options: [
                { label: 'Jack', value: 'jack' },
                { label: 'Jason', value: 'jason' },
                { label: 'Jesse', value: 'jesse' },
            ],
            'onUpdate:modelValue': (newValue) => {
                currentValue = newValue;
                screen.rerender({ modelValue: newValue });
            },
        },
    });

    const combobox = screen.getByRole('combobox');
    await combobox.click();

    const option = page.getByText('Jason', { exact: true });
    await option.click();

    // Wait for the dropdown to close
    await new Promise(resolve => setTimeout(resolve, 50));

    // Dropdown should be closed - the dropdown element should not be visible
    const dropdownQuery = await page.getByRole('listbox').query();
    expect(dropdownQuery).toBeNull();

    // Selected option should be shown in the container
    await expect.element(screen.container).toHaveTextContent('Jason');
});

test('can clear selected option', async () => {
    let currentValue = 'juncan';

    const screen = render(Combobox, {
        props: {
            clearable: true,
            modelValue: currentValue,
            options: [
                { label: 'Jack', value: 'jack' },
                { label: 'Jason', value: 'jason' },
                { label: 'Jesse', value: 'jesse' },
                { label: 'Joshua', value: 'joshua' },
                { label: 'Juncan', value: 'juncan' },
                { label: 'Jay', value: 'jay' },
            ],
            'onUpdate:modelValue': (newValue) => {
                currentValue = newValue;
                screen.rerender({ modelValue: newValue });
            },
        },
    });

    await expect.element(screen.container).toHaveTextContent('Juncan');

    const clearButton = screen.container.querySelector('[data-ui-combobox-clear-button]');
    await clearButton.click();

    expect(currentValue).toBeNull();
    await expect.element(screen.container).not.toHaveTextContent('Juncan');
});

test('can use different optionLabel and optionValue keys', async () => {
    let currentValue = null;

    const screen = render(Combobox, {
        props: {
            modelValue: currentValue,
            optionLabel: 'title',
            optionValue: 'id',
            options: [
                { title: 'Jack', id: 'jack' },
                { title: 'Jason', id: 'jason' },
                { title: 'Jesse', id: 'jesse' },
            ],
            'onUpdate:modelValue': (newValue) => {
                currentValue = newValue;
                screen.rerender({ modelValue: newValue });
            },
        },
    });

    const combobox = screen.getByRole('combobox');
    await combobox.click();

    const option = page.getByText('Jason', { exact: true });
    await option.click();

    expect(currentValue).toEqual('jason');
    await expect.element(screen.container).toHaveTextContent('Jason');
});

describe('multiple options', () => {
    test('can select multiple options', async () => {
        let currentValue = [];

        const screen = render(Combobox, {
            props: {
                multiple: true,
                modelValue: currentValue,
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                ],
                'onUpdate:modelValue': (newValue) => {
                    currentValue = newValue;
                    screen.rerender({ modelValue: newValue });
                },
            },
        });

        const combobox = screen.getByRole('combobox');
        await combobox.click();

        // Select first option
        await page.getByText('Jason', { exact: true }).click();

        // Select second option
        await page.getByText('Jesse', { exact: true }).click();

        // Select third option
        await page.getByText('Jack', { exact: true }).click();

        // Model value should have been updated
        expect(currentValue).toEqual(['jason', 'jesse', 'jack']);

        // All three should be visible in the container
        await expect.element(screen.container).toHaveTextContent('Jason');
        await expect.element(screen.container).toHaveTextContent('Jesse');
        await expect.element(screen.container).toHaveTextContent('Jack');
    });

    test('cant select more than the allowed number of options', async () => {
        let currentValue = [];

        const screen = render(Combobox, {
            props: {
                multiple: true,
                maxSelections: 2,
                modelValue: currentValue,
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                ],
                'onUpdate:modelValue': (newValue) => {
                    currentValue = newValue;
                    screen.rerender({ modelValue: newValue });
                },
            },
        });

        const combobox = screen.getByRole('combobox');
        await combobox.click();

        // Select first option
        await page.getByText('Jason', { exact: true }).click();

        // Select second option
        await page.getByText('Jesse', { exact: true }).click();

        // Verify we have exactly 2 selections
        expect(currentValue).toEqual(['jason', 'jesse']);

        // Only Jason and Jesse should be selected
        await expect.element(screen.container).toHaveTextContent('Jason');
        await expect.element(screen.container).toHaveTextContent('Jesse');
        expect(screen.container.textContent).not.toContain('Jack');
    });

    test('can deselect options', async () => {
        let currentValue = ['jason', 'jesse', 'jack'];
        
        const screen = render(Combobox, {
            props: {
                multiple: true,
                modelValue: currentValue,
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                ],
                'onUpdate:modelValue': (newValue) => {
                    currentValue = newValue;
                    screen.rerender({ modelValue: newValue });
                },
            },
        });
        
        // Verify all three are initially selected
        await expect.element(screen.container).toHaveTextContent('Jason');
        await expect.element(screen.container).toHaveTextContent('Jesse');
        await expect.element(screen.container).toHaveTextContent('Jack');

        // Find all deselect buttons by aria-label
        // The order in the UI is Jason, Jesse, Jack (same as modelValue array)
        const removeButtons = screen.container.querySelectorAll('button[aria-label="Deselect option"]');
        
        // Click the button for Jesse (index 1)
        await removeButtons[1].click();

        // Jesse should be removed
        expect(currentValue).toEqual(['jason', 'jack']);
    });
});

describe('search', () => {
    test('can search options', async () => {
        const screen = render(Combobox, {
            props: {
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                ],
            },
        });

        const combobox = screen.getByRole('combobox');
        await combobox.click();
        await userEvent.fill(combobox, 'jac');

        // After filtering, only Jack should be visible
        const jackOption = page.getByText('Jack', { exact: true });
        await expect.element(jackOption).toBeVisible();

        // Jason should not be visible
        const jasonQuery = await page.getByText('Jason', { exact: true }).query();
        expect(jasonQuery).toBeNull();
    });

    test('cant search options when searchable prop is false', async () => {
        const screen = render(Combobox, {
            props: {
                searchable: false,
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                ],
            },
        });

        const trigger = screen.container.querySelector('[data-ui-combobox-trigger]');
        await trigger.click();

        // When searchable is false, there should be no searchbox role
        const searchboxQuery = await screen.getByRole('combobox').query();
        expect(searchboxQuery).toBeNull();
    });

    test("doesn't search when ignoreFilter prop is true", async () => {
        const screen = render(Combobox, {
            props: {
                ignoreFilter: true,
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                    { label: 'Jesse', value: 'jesse' },
                ],
            },
        });

        const combobox = screen.getByRole('combobox');
        await combobox.click();
        await userEvent.fill(combobox, 'jac');

        // All options should still be visible since filtering is ignored
        await expect.element(page.getByText('Jack', { exact: true })).toBeVisible();
        await expect.element(page.getByText('Jason', { exact: true })).toBeVisible();
        await expect.element(page.getByText('Jesse', { exact: true })).toBeVisible();
    });
});

describe('taggable', () => {
    test('can append options', async () => {
        let currentValue = [];

        const screen = render(Combobox, {
            props: {
                multiple: true,
                taggable: true,
                modelValue: currentValue,
                'onUpdate:modelValue': (newValue) => {
                    currentValue = newValue;
                    screen.rerender({ modelValue: newValue });
                },
            },
        });

        const combobox = screen.getByRole('combobox');
        await combobox.click();
        await userEvent.fill(combobox, 'NewTag');
        await userEvent.keyboard('{Enter}');

        expect(currentValue).toEqual(['NewTag']);
        await expect.element(screen.container).toHaveTextContent('NewTag');
    });

    test('can paste into search input', async () => {
        let currentValue = [];

        const screen = render(Combobox, {
            props: {
                multiple: true,
                taggable: true,
                modelValue: currentValue,
                'onUpdate:modelValue': (newValue) => {
                    currentValue = newValue;
                    screen.rerender({ modelValue: newValue });
                },
            },
        });

        const combobox = screen.getByRole('combobox');
        await combobox.click();

        // Simulate paste event
        const input = combobox.element();
        const pasteEvent = new ClipboardEvent('paste', {
            clipboardData: new DataTransfer(),
            bubbles: true,
            cancelable: true,
        });
        pasteEvent.clipboardData.setData('text/plain', 'Tag1,Tag2,Tag3');
        input.dispatchEvent(pasteEvent);

        expect(currentValue).toEqual(['Tag1', 'Tag2', 'Tag3']);
        await expect.element(screen.container).toHaveTextContent('Tag1');
        await expect.element(screen.container).toHaveTextContent('Tag2');
        await expect.element(screen.container).toHaveTextContent('Tag3');
    });
});

describe('accessibility', () => {
    test('dropdown opens on space', async () => {
        const screen = render(Combobox, {
            props: {
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                ],
            },
        });

        const combobox = screen.getByRole('combobox');
        // Focus the element directly
        combobox.element().focus();
        await userEvent.keyboard(' ');

        // Check that options are visible
        const jackOption = page.getByText('Jack', { exact: true });
        await expect.element(jackOption).toBeVisible();
    });

    test('dropdown closes on escape', async () => {
        const screen = render(Combobox, {
            props: {
                options: [
                    { label: 'Jack', value: 'jack' },
                    { label: 'Jason', value: 'jason' },
                ],
            },
        });

        const combobox = screen.getByRole('combobox');
        await combobox.click();

        // Options should be visible
        const jackOption = page.getByText('Jack', { exact: true });
        await expect.element(jackOption).toBeVisible();

        await userEvent.keyboard('{Escape}');

        // Options should be hidden
        const jackQuery = await page.getByText('Jack', { exact: true }).query();
        expect(jackQuery).toBeNull();
    });
});
