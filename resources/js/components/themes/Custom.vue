<script setup lang="ts">
import { Button, Description, Input, Table, TableCell, TableColumn, TableColumns, TableRow, TableRows } from '@ui';
import { computed } from 'vue';
import { ColorVariableName, Theme, CompleteTheme, ThemeColors } from './types';
import Preview from './Preview.vue';
import { getDefaultTheme, colors } from '.';
import { translate as __ } from '@/translations/translator';
import ColorPicker from '@/components/themes/color-picker/ColorPicker.vue';
import Share from '@/components/themes/Share.vue';

const props = defineProps<{
    modelValue?: Theme;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', theme: Theme): void;
    (e: 'shared'): void;
}>();

const theme = computed<CompleteTheme>(() => {
    const defaultTheme = getDefaultTheme();

    if (!props.modelValue) return defaultTheme;

    return {
        id: props.modelValue.id,
        name: props.modelValue.name,
        colors: { ...defaultTheme.colors, ...props.modelValue.colors },
        darkColors: { ...defaultTheme.darkColors, ...props.modelValue.darkColors }
    };
});

function updateColor(colorName: ColorVariableName, value: string, isDark: boolean = false) {
    const colors = { ...theme.value.colors };
    const darkColors = { ...theme.value.darkColors };

    if (isDark) {
        darkColors[colorName] = value;
    } else {
        colors[colorName] = value;
    }

    updateColors(colors, darkColors);
}

function hasLightColor(colorName: ColorVariableName): boolean {
    const defaultTheme = getDefaultTheme();
    return theme.value.colors[colorName] !== defaultTheme.colors[colorName];
}

function hasDarkColor(colorName: ColorVariableName): boolean {
    return Boolean(props.modelValue?.darkColors?.[colorName]);
}

function clearLightColor(colorName: ColorVariableName) {
    const defaultTheme = getDefaultTheme();
    const colors = { ...theme.value.colors, [colorName]: defaultTheme.colors[colorName] };
    updateColors(colors, theme.value.darkColors);
}

function clearDarkColor(colorName: ColorVariableName) {
    const { [colorName]: _, ...darkColors } = theme.value.darkColors;
    updateColors(theme.value.colors, darkColors);
}

function addDarkColor(colorName: ColorVariableName) {
    updateColors(theme.value.colors, {
        ...theme.value.darkColors, [colorName]: theme.value.colors[colorName]
    });
}

function updateColors(colors: ThemeColors, darkColors: ThemeColors) {
    emit('update:modelValue', {
        id: 'custom',
        name: 'Custom',
        colors,
        darkColors
    });
}

const sharable = computed(() => theme.value.id === 'custom'
    && (Object.keys(props.modelValue.colors).length || Object.keys(props.modelValue.darkColors).length));
</script>

<template>
    <div class="grid grid-cols-[3fr_2fr] gap-10">
        <div class="">
            <Table>
                <TableColumns>
                    <TableColumn>{{ __('Color') }}</TableColumn>
                    <TableColumn>{{ __('Light') }}</TableColumn>
                    <TableColumn>{{ __('Dark') }}</TableColumn>
                </TableColumns>
                <TableRows>
                    <TableRow v-for="color in colors" :key="color.name">
                        <TableCell>
                            <Description :text="color.label" class="flex-1" />
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center w-16">
                                <ColorPicker
                                    :model-value="theme.colors[color.name]"
                                    @update:model-value="updateColor(color.name, $event, false)"
                                />
                                <Button
                                    v-if="hasLightColor(color.name)"
                                    icon="x"
                                    variant="ghost"
                                    size="sm"
                                    @click="clearLightColor(color.name)"
                                />
                            </div>
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center w-16">
                                <ColorPicker
                                    v-if="hasDarkColor(color.name)"
                                    :model-value="theme.darkColors[color.name]"
                                    @update:model-value="updateColor(color.name, $event, true)"
                                />
                                <Button
                                    v-if="hasDarkColor(color.name)"
                                    icon="x"
                                    variant="ghost"
                                    size="sm"
                                    @click="clearDarkColor(color.name)"
                                />
                                <Button
                                    v-else
                                    icon="plus"
                                    variant="ghost"
                                    size="sm"
                                    @click="addDarkColor(color.name)"
                                />
                            </div>
                        </TableCell>
                    </TableRow>
                </TableRows>
            </Table>
        </div>
        <div class="sticky top-0 self-start space-y-4">
            <Preview :theme="theme" appearance="light" />
            <Preview :theme="theme" appearance="dark" />

            <Share v-if="sharable" :theme="theme" @shared="emit('shared')" />
        </div>
    </div>
</template>
