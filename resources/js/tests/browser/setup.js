import '../setup.js';

import { config as browserConfig } from 'vitest-browser-vue';

import '../../../css/app.css';
import '../../../css/ui.css';

browserConfig.global.mocks = {
    __: (key) => key,
};

browserConfig.global.directives = {
    tooltip: () => {},
};

if (typeof window !== 'undefined') {
    window.__ = (key) => key;
}

if (typeof document !== 'undefined') {
    const style = document.createElement('style');
    style.textContent = `
        :root {
            /* Basic theme colors */
            --theme-color-primary: #3b82f6;
            --theme-color-success: #10b981;
            --theme-color-gray-50: #f9fafb;
            --theme-color-gray-100: #f3f4f6;
            --theme-color-gray-150: #e8ebef;
            --theme-color-gray-200: #e5e7eb;
            --theme-color-gray-300: #d1d5db;
            --theme-color-gray-400: #9ca3af;
            --theme-color-gray-500: #6b7280;
            --theme-color-gray-600: #4b5563;
            --theme-color-gray-700: #374151;
            --theme-color-gray-800: #1f2937;
            --theme-color-gray-850: #1a202c;
            --theme-color-gray-900: #111827;
            --theme-color-gray-925: #0d1117;

            /* UI specific colors */
            --theme-color-body-bg: #ffffff;
            --theme-color-body-border: #e5e7eb;
            --theme-color-content-border: #e5e7eb;
            --theme-color-focus-outline: #3b82f6;

            /* Z-index values */
            --z-index-above: 10;
            --z-index-portal: 1000;
            --z-index-modal: 1050;
        }

        /* Ensure body has a white background */
        body {
            background-color: white;
            color: #111827;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Basic button/input styling for visibility */
        button, input, select {
            font-family: inherit;
        }
    `;
    document.head.appendChild(style);
}