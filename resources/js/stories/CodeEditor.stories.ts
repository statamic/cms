import type {Meta, StoryObj} from '@storybook/vue3';
import {CodeEditor} from '@ui';
import {ref} from 'vue';

const meta = {
    title: 'Components/CodeEditor',
    component: CodeEditor,
    argTypes: {
        indentType: {
            control: 'select',
            options: ['tabs', 'spaces'],
        },
        keyMap: {
            control: 'select',
            options: ['sublime', 'vim'],
        },
        mode: {
            control: 'select',
            options: ['clike', 'css', 'diff', 'go', 'haml', 'handlebars', 'htmlmixed', 'less', 'markdown', 'gfm', 'nginx', 'text/x-java', 'javascript', 'jsx', 'text/x-objectivec', 'php', 'python', 'ruby', 'scss', 'shell', 'sql', 'twig', 'vue', 'xml', 'yaml-frontmatter'],
        },
        colorMode: {
            control: 'select',
            options: ['system', 'light', 'dark'],
        },
        'update:mode': {
            description: 'Event handler called when the syntax highlighting mode changes.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' }
            }
        },
        'update:modelValue': {
            description: 'Event handler called when the code editor value changes.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' }
            }
        },
    },
} satisfies Meta<typeof CodeEditor>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<CodeEditor mode="javascript" v-model="code" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { CodeEditor },
        setup() {
            const code = ref('function hello() {\n    console.log("Hello, world!");\n}');
            return { code };
        },
        template: `<CodeEditor mode="javascript" v-model="code" /><PortalTargets />`,
    }),
};

const modesCode = `
<div class="space-y-4">
    <CodeEditor mode="javascript" v-model="jsCode" />
    <CodeEditor mode="php" v-model="phpCode" />
    <CodeEditor mode="yaml" v-model="yamlCode" />
</div>
`;

export const _Modes: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: modesCode }
        }
    },
    render: () => ({
        components: { CodeEditor },
        setup() {
            const jsCode = ref('const greeting = "Hello";');
            const phpCode = ref('<?php\n$greeting = "Hello";\necho $greeting;');
            const yamlCode = ref('title: My Site\nurl: https://example.com');
            return { jsCode, phpCode, yamlCode };
        },
        template: `
            <div class="space-y-4">
                <CodeEditor mode="javascript" v-model="jsCode" />
                <CodeEditor mode="php" v-model="phpCode" />
                <CodeEditor mode="yaml" v-model="yamlCode" />
            </div>
            <PortalTargets />
        `,
    }),
};

const modeSelectionCode = `
<CodeEditor
    v-model="code"
    :mode="mode"
    :allow-mode-selection="false"
    @update:mode="mode = $event"
/>
`;

export const _ModeSelection: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: modeSelectionCode }
        }
    },
    render: () => ({
        components: { CodeEditor },
        setup() {
            const code = ref('console.log("Hello");');
            const mode = ref('javascript');
            return { code, mode };
        },
        template: `<CodeEditor v-model="code" :mode="mode" :allow-mode-selection="false" @update:mode="mode = $event" /><PortalTargets />`,
    }),
};

const noLineNumbersCode = `
<CodeEditor
    mode="markdown"
    v-model="markdown"
    :line-numbers="false"
/>
`;

export const _NoLineNumbers: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: noLineNumbersCode }
        }
    },
    render: () => ({
        components: { CodeEditor },
        setup() {
            const markdown = ref('# Hello\n\nThis is a markdown editor without line numbers.');
            return { markdown };
        },
        template: `<CodeEditor mode="markdown" v-model="markdown" :line-numbers="false" /><PortalTargets />`,
    }),
};
