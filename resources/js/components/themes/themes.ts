import { PredefinedTheme } from '@/components/themes/types';

export const nativeThemes = [
    {
        id: 'default',
        name: 'Default',
        author: 'Statamic',
        colors: {
            'primary': 'oklch(0.457 0.24 277.023)',
            'ui-accent-bg': 'oklch(0.457 0.24 277.023)',
        },
    },
    {
        id: 'zinc',
        name: 'Zinc',
        author: 'Statamic',
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
        author: 'Statamic',
        colors: {
            'primary': 'oklch(0.21 0.006 285.885)',
            'switch-bg': 'oklch(0.723 0.219 149.579)',
        },
    }
] as PredefinedTheme[];
