import type { CompleteTheme, ThemeValue, ThemeColors, ColorVariableName, ColorDefinition, Theme } from './types';
import { config } from '@api';

export const defaultTheme = {
    id: 'default',
    name: 'Default',
    author: 'Statamic',
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

export function toSelectionValue(theme: Theme | null): Theme | null {
    if (! theme) return null;

    const cleanedTheme = removeDefaults(theme);

    if (Object.keys(cleanedTheme.colors).length === 0 && Object.keys(cleanedTheme.darkColors).length === 0) {
        return null;
    }

    return cleanedTheme;
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

export function removeDefaults(theme: Theme): Theme {
    const defaultTheme = getDefaultTheme();
    const colors: ThemeColors = {};
    const darkColors: ThemeColors = {};

    for (const [colorName, colorValue] of Object.entries(theme.colors || {})) {
        if (colorValue !== defaultTheme.colors[colorName]) {
            colors[colorName] = colorValue;
        }
    }

    for (const [colorName, colorValue] of Object.entries(theme.darkColors || {})) {
        if (colorValue !== defaultTheme.darkColors[colorName]) {
            darkColors[colorName] = colorValue;
        }
    }

    return {
        ...theme,
        colors,
        darkColors,
    };
}

export function addDefaults(theme: Theme): CompleteTheme {
    const defaultTheme = getDefaultTheme();

    return {
        ...theme,
        colors: { ...defaultTheme.colors, ...theme.colors },
        darkColors: { ...defaultTheme.darkColors, ...theme.darkColors },
    };
}

export function themesDeviate(themeA: Theme, themeB: Theme): boolean {
    themeA = addDefaults(themeA);
    themeB = addDefaults(themeB);

    return objectsDiffer(themeA.colors ?? {}, themeB.colors ?? {})
        || objectsDiffer(themeA.darkColors ?? {}, themeB.darkColors ?? {});
}

function objectsDiffer(a: object, b: object): boolean {
    const allKeys = new Set([...Object.keys(a), ...Object.keys(b)]);
    for (const key of allKeys) {
        if (a[key] !== b[key]) {
            return true;
        }
    }
    return false;
}

export const grayPalettes = {
    slate: {
        50: 'oklch(0.984 0.003 247.858)',
        100: 'oklch(0.968 0.007 247.896)',
        150: 'oklch(0.9485 0.01 251.702)',
        200: 'oklch(0.929 0.013 255.508)',
        300: 'oklch(0.869 0.022 252.894)',
        400: 'oklch(0.704 0.04 256.788)',
        500: 'oklch(0.554 0.046 257.417)',
        600: 'oklch(0.446 0.043 257.281)',
        700: 'oklch(0.372 0.044 257.287)',
        800: 'oklch(0.279 0.041 260.031)',
        850: 'oklch(0.236 0.041 263.801)',
        900: 'oklch(0.208 0.042 265.755)',
        925: 'oklch(0.1945 0.042 265.573)',
        950: 'oklch(0.169 0.042 264.695)',
    },
    gray: {
        50: 'oklch(0.985 0.002 247.839)',
        100: 'oklch(0.967 0.003 264.542)',
        150: 'oklch(0.9475 0.0045 264.536)',
        200: 'oklch(0.928 0.006 264.531)',
        300: 'oklch(0.872 0.01 258.338)',
        400: 'oklch(0.707 0.022 261.325)',
        500: 'oklch(0.551 0.027 264.364)',
        600: 'oklch(0.446 0.03 256.802)',
        700: 'oklch(0.373 0.034 259.733)',
        800: 'oklch(0.278 0.033 256.848)',
        850: 'oklch(0.236 0.033 260.201)',
        900: 'oklch(0.21 0.034 264.665)',
        925: 'oklch(0.1963 0.0330 264.157)',
        950: 'oklch(0.18 0.028 261.692)',
    },
    zinc: {
        50: 'oklch(0.985 0 0)',
        100: 'oklch(0.967 0.001 286.375)',
        150: 'oklch(0.956 0.0022 286.32)',
        200: 'oklch(0.92 0.004 286.32)',
        300: 'oklch(0.871 0.006 286.286)',
        400: 'oklch(0.705 0.015 286.067)',
        500: 'oklch(0.552 0.016 285.938)',
        600: 'oklch(0.442 0.017 285.786)',
        700: 'oklch(0.37 0.013 285.805)',
        800: 'oklch(0.274 0.006 286.033)',
        850: 'oklch(0.236 0.006 286.015)',
        900: 'oklch(0.21 0.006 285.885)',
        925: 'oklch(0.1982 0.0042 285.73)',
        950: 'oklch(0.141 0.005 285.823)',
    },
    neutral: {
        50: 'oklch(0.985 0 0)',
        100: 'oklch(0.97 0 0)',
        150: 'oklch(0.946 0 0)',
        200: 'oklch(0.922 0 0)',
        300: 'oklch(0.87 0 0)',
        400: 'oklch(0.708 0 0)',
        500: 'oklch(0.556 0 0)',
        600: 'oklch(0.439 0 0)',
        700: 'oklch(0.371 0 0)',
        800: 'oklch(0.269 0 0)',
        850: 'oklch(0.236 0 0)',
        900: 'oklch(0.205 0 0)',
        925: 'oklch(0.1947 0 0)',
        950: 'oklch(0.145 0 0)',
    },
    stone: {
        50: 'oklch(0.985 0.001 106.423)',
        100: 'oklch(0.97 0.001 106.424)',
        150: 'oklch(0.9465 0.002 77.571)',
        200: 'oklch(0.923 0.003 48.717)',
        300: 'oklch(0.869 0.005 56.366)',
        400: 'oklch(0.709 0.01 56.259)',
        500: 'oklch(0.553 0.013 58.071)',
        600: 'oklch(0.444 0.011 73.639)',
        700: 'oklch(0.374 0.01 67.558)',
        800: 'oklch(0.268 0.007 34.298)',
        850: 'oklch(0.236 0.006 48.043)',
        900: 'oklch(0.216 0.006 56.043)',
        925: 'oklch(0.2042 0.0057 55.203)',
        950: 'oklch(0.187 0.004 49.25)',
    },
} as const;

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
