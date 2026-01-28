import type { KnipConfig } from 'knip';
import { parse, type SFCScriptBlock, type SFCStyleBlock } from 'vue/compiler-sfc';

function getScriptBlockContent(block: SFCScriptBlock | null): string[] {
    if (!block) return [];
    if (block.src) return [`import '${block.src}'`];
    return [block.content];
}

function getStyleBlockContent(block: SFCStyleBlock | null): string[] {
    if (!block) return [];
    if (block.src) return [`@import '${block.src}';`];
    return [block.content];
}

function getStyleImports(content: string): string {
    return [...content.matchAll(/(?<=@)import[^;]+/g)].join('\n');
}

const config = {
    entry: ['resources/js/{index,exports}.js', 'resources/js/frontend/helpers.js'],
    project: ['resources/js/**/*.{js,vue}'],
    compilers: {
        vue: (text: string, filename: string) => {
            const { descriptor } = parse(text, { filename, sourceMap: false });
            return [
                ...getScriptBlockContent(descriptor.script),
                ...getScriptBlockContent(descriptor.scriptSetup),
                ...descriptor.styles.flatMap(getStyleBlockContent).map(getStyleImports),
            ].join('\n');
        },
    },
} satisfies KnipConfig;

export default config;
