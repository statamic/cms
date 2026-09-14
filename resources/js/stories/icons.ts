/**
 * Storybook icon utilities
 */

// @ts-ignore - Vite's import.meta.glob is not recognized by TypeScript
const iconFiles = import.meta.glob('../../svg/icons/*.svg');

export const icons = Object.keys(iconFiles).map((path) => {
    const parts = path.split('/');
    const fileName = parts[parts.length - 1];
    return fileName.replace('.svg', '');
}).sort();