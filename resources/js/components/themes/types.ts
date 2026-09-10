import { grayPalettes } from '.';

export type GrayPalette = keyof typeof grayPalettes;

export type ColorVariableName =
    | 'primary'
    | 'success'
    | 'danger'
    | 'body-bg'
    | 'body-border'
    | 'content-bg'
    | 'content-border'
    | 'global-header-bg'
    | 'progress-bar'
    | 'focus-outline'
    | 'ui-accent-bg'
    | 'ui-accent-text'
    | 'switch-bg'
    | 'chart-1'
    | 'chart-2'
    | 'chart-3'
    | 'chart-4'
    | 'chart-5'
    | 'chart-1-label-bg'
    | 'chart-2-label-bg'
    | 'chart-3-label-bg'
    | 'chart-4-label-bg'
    | 'chart-5-label-bg'
    | 'chart-1-legend'
    | 'chart-2-legend'
    | 'chart-3-legend'
    | 'chart-4-legend'
    | 'chart-5-legend'
    | 'gray-50'
    | 'gray-100'
    | 'gray-150'
    | 'gray-200'
    | 'gray-300'
    | 'gray-400'
    | 'gray-500'
    | 'gray-600'
    | 'gray-700'
    | 'gray-800'
    | 'gray-850'
    | 'gray-900'
    | 'gray-925'
    | 'gray-950'

export type ColorValue = string;

export type ColorDefinition = {
    name: ColorVariableName;
    label: string;
}

export type ThemeColors = {
    [K in ColorVariableName]?: ColorValue;
};

export type Theme = {
    id: string;
    name: string;
    description?: string;
    author?: string;
    colors?: Partial<ThemeColors>;
    darkColors?: Partial<ThemeColors>;
}

export type CompleteTheme = Omit<Theme, 'colors' | 'darkColors'> & {
    colors: ThemeColors;
    darkColors: ThemeColors;
}

export type ThemeValue = {
    id?: string;
    name?: string;
    colors: { [key: string]: ColorValue };
}
