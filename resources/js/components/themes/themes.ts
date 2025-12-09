import { PredefinedTheme } from '@/components/themes/types';

export const nativeThemes = [
    {
        id: 'default',
        name: 'Default',
        colors: {
            'primary': 'oklch(0.457 0.24 277.023)',
            'ui-accent-bg': 'oklch(0.457 0.24 277.023)',
        },
    },
    {
        id: 'zinc',
        name: 'Zinc',
        colors: {
            'primary': 'oklch(0.21 0.006 285.885)',
            'ui-accent-bg': 'oklch(0.21 0.006 285.885)',
        },
        darkColors: {
            'primary': 'oklch(0.37 0.013 285.805)',
        }
    },
    {
        id: 'zinc-green-switch',
        name: 'Zinc + Green',
        colors: {
            'primary': 'oklch(0.21 0.006 285.885)',
            'switch-bg': 'oklch(0.723 0.219 149.579)',
        },
    }
] as PredefinedTheme[];

export const marketplaceThemes = [
    {
        id: 'filament',
        name: 'Filament',
        colors: {
            'global-header-bg': 'oklch(0.666 0.179 58.318)',
            'primary': 'oklch(0.828 0.189 84.429)',
            'switch-bg': 'oklch(0.828 0.189 84.429)',
        },
    },
    {
        id: 'peak',
        name: 'Peak',
        colors: {
            'primary': 'oklch(0.546 0.245 262.881)',
            'gray-50': 'oklch(0.984 0.003 247.858)',
            'gray-100': 'oklch(0.968 0.007 247.896)',
            'gray-150': 'oklch(0.9485 0.01 251.702)',
            'gray-200': 'oklch(0.929 0.013 255.508)',
            'gray-300': 'oklch(0.869 0.022 252.894)',
            'gray-400': 'oklch(0.704 0.04 256.788)',
            'gray-500': 'oklch(0.554 0.046 257.417)',
            'gray-600': 'oklch(0.446 0.043 257.281)',
            'gray-700': 'oklch(0.372 0.044 257.287)',
            'gray-800': 'oklch(0.279 0.041 260.031)',
            'gray-850': 'oklch(0.236 0.041 263.801)',
            'gray-900': 'oklch(0.208 0.042 265.755)',
            'gray-925': 'oklch(0.1945 0.042 265.573)',
            'gray-950': 'oklch(0.169 0.042 264.695)',
            'success': 'oklch(0.792 0.209 151.711)',
            'danger': 'oklch(0.577 0.245 27.325)',
            'body-bg': '#fff',
            'body-border': 'transparent',
            'content-bg': 'oklch(0.984 0.003 247.858)',
            'content-border': 'oklch(0.92 0.004 286.32)',
            'global-header-bg': 'oklch(0.274 0.006 286.033)',
            'progress-bar': 'oklch(93.86% 0.2018 122.24)',
            'focus-outline': 'oklch(0.707 0.165 254.624)',
            'ui-accent-bg': 'oklch(0.457 0.24 277.023)',
            'ui-accent-text': 'var(--theme-color-ui-accent-bg)',
            'switch-bg': 'oklch(0.627 0.194 149.214)',
        },
        darkColors: {
            'gray-50': 'oklch(0.985 0 0)',
            'gray-100': 'oklch(0.967 0.001 286.375)',
            'gray-150': 'oklch(0.956 0.0022 286.32)',
            'gray-200': 'oklch(0.92 0.004 286.32)',
            'gray-300': 'oklch(0.871 0.006 286.286)',
            'gray-400': 'oklch(0.705 0.015 286.067)',
            'gray-500': 'oklch(0.552 0.016 285.938)',
            'gray-600': 'oklch(0.442 0.017 285.786)',
            'gray-700': 'oklch(0.37 0.013 285.805)',
            'gray-800': 'oklch(0.274 0.006 286.033)',
            'gray-850': 'oklch(0.236 0.006 286.015)',
            'gray-900': 'oklch(0.21 0.006 285.885)',
            'gray-925': 'oklch(0.1982 0.0042 285.73)',
            'gray-950': 'oklch(0.141 0.005 285.823)',
            'body-bg': 'oklch(0.21 0.006 285.885)',
            'body-border': 'oklch(0.141 0.005 285.823)',
            'content-bg': 'oklch(0.21 0.006 285.885)',
            'content-border': 'oklch(0.141 0.005 285.823)',
            'ui-accent-text': 'oklch(0.673 0.182 276.935)',
            'switch-bg': 'oklch(0.627 0.194 149.214)',
        }
    },
    {
        name: 'My Eyes',
        id: 'my-eyes',
        colors: {
            'primary': 'lime',
            'gray-50': 'black',
            'gray-100': 'black',
            'gray-200': 'black',
            'gray-300': 'black',
            'gray-400': 'black',
            'gray-500': 'black',
            'gray-600': 'lime',
            'gray-700': 'lime',
            'gray-800': 'lime',
            'gray-850': 'lime',
            'gray-900': 'lime',
            'gray-925': 'lime',
            'gray-950': 'lime',
            'success': 'lime',
            'danger': 'black',
            'body-bg': 'black',
            'body-border': 'transparent',
            'content-bg': 'lime',
            'content-border': 'black',
            'global-header-bg': 'black',
            'progress-bar': 'lime',
            'ui-accent-bg': 'lime',
            'ui-accent-text': 'lime',
            'switch-bg': 'lime',
        },
        darkColors: {
            'dark-body-bg': 'black',
            'dark-body-border': 'black',
            'dark-content-bg': 'black',
            'dark-content-border': 'black',
            'dark-global-header-bg': 'black',
            'dark-ui-accent-bg': 'lime',
            'dark-ui-accent-text': 'lime',
            'dark-switch-bg': 'lime',
        }
    }
] as PredefinedTheme[];
