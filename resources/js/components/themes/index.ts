import type { CompleteTheme, ThemeValue, ThemeColors, ColorVariableName, ColorDefinition, Theme } from './types';
import { config } from '@api';

export const defaultTheme = {
    id: 'default',
    name: 'Default',
    author: 'Statamic',
    colors: {
        'primary': 'oklch(0.457 0.24 277.023)',
        'ui-accent-bg': 'oklch(0.457 0.24 277.023)',
    }
} as Theme;

export function getDefaultTheme(): CompleteTheme {
    const defaultTheme = config.get('defaultTheme');

    return {
        id: defaultTheme?.id || 'default',
        name: defaultTheme?.name || 'Default',
        colors: defaultTheme?.light || {},
        darkColors: defaultTheme?.dark || {}
    }
}

export function valueToTheme(value: ThemeValue | null): Theme | null {
    if (!value) return null;

    const colors: ThemeColors = {};
    const darkColors: ThemeColors = {};

    Object.entries(value.colors).forEach(([key, colorValue]) => {
        if (key.startsWith('dark-')) {
            const colorName = key.substring(5) as ColorVariableName;
            darkColors[colorName] = colorValue;
        } else {
            colors[key as ColorVariableName] = colorValue;
        }
    });

    return {
        id: value.id ?? 'custom',
        name: value.name,
        colors,
        darkColors,
    };
}

export function applyTheme(theme: Theme): void {
    const styleTag = document.getElementById('theme-colors');

    const defaults = getDefaultTheme();
    const mergedColors = { ...defaults.colors, ...theme.colors };
    const mergedDarkColors = { ...defaults.darkColors, ...theme.darkColors };
    const { light, dark } = getCssVariables(mergedColors, mergedDarkColors);

    styleTag.textContent = `:root {
        ${light}

        &.dark {
            ${dark}
        }
    }`;
}

export function applyDefaultTheme(): void {
    applyTheme({ id: 'default', name: 'Default', colors: {} });
}

export function getCssVariables(colors: ThemeColors, darkColors: ThemeColors): { light: string; dark: string } {
    const light = Object.entries(colors)
        .filter(([_, value]) => value)
        .map(([key, value]) => `--theme-color-${key}: ${value};`)
        .join('\n');

    const dark = Object.entries(darkColors)
        .filter(([_, value]) => value)
        .map(([key, value]) => {
            const colorName = key.startsWith('dark-') ? key.substring(5) : key;
            return `--theme-color-${colorName}: ${value};`;
        })
        .join('\n');

    return { light, dark };
}

export const colors = [
    { name: 'primary', label: 'Primary', },
    { name: 'global-header-bg', label: 'Global Header Background', },
    { name: 'body-bg', label: 'Body Background', },
    { name: 'body-border', label: 'Body Border', },
    { name: 'content-bg', label: 'Content Background', },
    { name: 'content-border', label: 'Content Border', },
    { name: 'progress-bar', label: 'Progress Bar', },
    { name: 'focus-outline', label: 'Focus Outline', },
    { name: 'ui-accent-bg', label: 'Accent Background', },
    { name: 'ui-accent-text', label: 'Accent Text', },
    { name: 'switch-bg', label: 'Switch Background', },
    { name: 'success', label: 'Success', },
    { name: 'danger', label: 'Danger', },
    { name: 'gray-50', label: 'Gray 50', },
    { name: 'gray-100', label: 'Gray 100', },
    { name: 'gray-150', label: 'Gray 150', },
    { name: 'gray-200', label: 'Gray 200', },
    { name: 'gray-300', label: 'Gray 300', },
    { name: 'gray-400', label: 'Gray 400', },
    { name: 'gray-500', label: 'Gray 500', },
    { name: 'gray-600', label: 'Gray 600', },
    { name: 'gray-700', label: 'Gray 700', },
    { name: 'gray-800', label: 'Gray 800', },
    { name: 'gray-850', label: 'Gray 850', },
    { name: 'gray-900', label: 'Gray 900', },
    { name: 'gray-925', label: 'Gray 925', },
    { name: 'gray-950', label: 'Gray 950', },
] as ColorDefinition[];
