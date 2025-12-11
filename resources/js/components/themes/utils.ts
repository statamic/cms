import type { Theme, ThemeValue, ThemeColors, ColorVariableName } from './types';
import { config } from '@api';

export function getDefaultTheme(): Theme {
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
