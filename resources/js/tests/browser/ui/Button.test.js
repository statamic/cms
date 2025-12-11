import { expect, test } from 'vitest';
import { render } from 'vitest-browser-vue';
import { Button } from '@ui';

test('renders button with text prop', async () => {
    const screen = render(Button, {
        props: {
            text: 'Click Me',
        },
    });

    expect(screen.container.textContent).toContain('Click Me');
});

test('renders button with slot', async () => {
    const screen = render(Button, {
        slots: {
            default: `Hello, World!`,
        },
    });

    expect(screen.container.textContent).toContain('Hello, World!');
});

test('can click button', async () => {
    let clicked = false;
    
    const screen = render(Button, {
        props: {
            text: 'Click Me',
            onClick: () => { clicked = true; },
        },
    });

    const button = screen.getByRole('button');
    await button.click();
    
    expect(clicked).toBe(true);
});

test('disabled button', async () => {
    const screen = render(Button, {
        props: {
            text: 'Click Me',
            disabled: true,
        },
    });

    const button = screen.getByRole('button');
    
    await expect.element(button).toBeDisabled();
});
