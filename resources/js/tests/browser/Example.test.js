import { describe, it, expect } from 'vitest';
import { page } from 'vitest/browser';

it('can interact with the DOM', async () => {
    // Create a simple element
    const container = document.createElement('div');
    container.innerHTML = '<button id="test-btn">Click me</button>';
    document.body.appendChild(container);

    const button = page.getByRole('button', { name: 'Click me' });
    await expect.element(button).toBeVisible()

    // Clean up
    container.remove();
});
