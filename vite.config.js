import { defineConfig, loadEnv } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { visualizer } from 'rollup-plugin-visualizer';
import svgLoader from 'vite-svg-loader';
import path from 'path';
import { writeFileSync } from 'fs';

function generateTailwindExclusions(isProdBuild) {
    return {
        name: 'generate-tailwind-exclusions',
        writeBundle(options, bundle) {
            // Only generate on production builds
            if (!isProdBuild) return;
            
            try {
                // Find the main CSS asset
                const cssFile = Object.keys(bundle).find(file => 
                    file.startsWith('assets/app-') && file.endsWith('.css')
                );
                
                if (!cssFile) {
                    console.warn('⚠️  Could not find main CSS file to generate Tailwind exclusions');
                    return;
                }
                
                const cssAsset = bundle[cssFile];
                const cssContent = cssAsset.source || cssAsset.code;
                const tailwindClasses = extractTailwindUtilities(cssContent);
                
                // Generate exclusion CSS
                const exclusionCSS = `@source not inline("${tailwindClasses.join(' ')}");`;
                
                // Write to package directory for distribution
                writeFileSync('resources/js/package/tailwind-exclusions.css', exclusionCSS);
                
                console.log(`✅ Generated ${tailwindClasses.length} Tailwind exclusions for addon developers`);
                
            } catch (error) {
                console.warn('⚠️  Failed to generate Tailwind exclusions:', error.message);
            }
        }
    };
}

function extractTailwindUtilities(cssContent) {
    const utilities = new Set();
    
    // More comprehensive patterns to extract Tailwind utilities from CSS
    const patterns = [
        // Look for all class definitions starting with common Tailwind prefixes
        /\.(?:absolute|relative|static|fixed|sticky)\b/g,
        /\.(?:block|inline-block|inline|flex|grid|hidden|visible)\b/g,
        /\.(?:p|m|px|py|mx|my|ml|mr|mt|mb|ms|me)-(?:\d+|px|auto|\d+\.\d+)\b/g,
        /\.(?:bg|text|border)-(?:white|black|transparent|current|inherit)\b/g,
        /\.(?:bg|text|border)-(?:gray|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-(?:\d+)\b/g,
        /\.text-(?:xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl)\b/g,
        /\.font-(?:thin|extralight|light|normal|medium|semibold|bold|extrabold|black)\b/g,
        /\.leading-(?:\d+|none|tight|snug|normal|relaxed|loose)\b/g,
        /\.tracking-(?:tighter|tight|normal|wide|wider|widest)\b/g,
        /\.(?:border|border-t|border-r|border-b|border-l|border-x|border-y)(?:-\d+)?\b/g,
        /\.rounded(?:-(?:none|sm|md|lg|xl|2xl|3xl|full|t|r|b|l|tl|tr|br|bl))?\b/g,
        /\.shadow(?:-(?:sm|md|lg|xl|2xl|inner|none))?\b/g,
        /\.opacity-(?:\d+)\b/g,
        /\.(?:w|h)-(?:\d+|px|auto|full|screen|min|max|fit|\d+\.\d+)\b/g,
        /\.max-w-(?:none|xs|sm|md|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|full|min|max|fit|prose|screen-sm|screen-md|screen-lg|screen-xl|screen-2xl)\b/g,
        /\.(?:min|max)-h-(?:\d+|px|full|screen|min|max|fit)\b/g,
        /\.justify-(?:normal|start|end|center|between|around|evenly|stretch)\b/g,
        /\.items-(?:start|end|center|baseline|stretch)\b/g,
        /\.flex-(?:1|auto|initial|none|col|row|col-reverse|row-reverse|wrap|wrap-reverse|nowrap)\b/g,
        /\.gap-(?:\d+|px|\d+\.\d+)\b/g,
        /\.(?:space-x|space-y)-(?:\d+|px|\d+\.\d+)\b/g,
        /\.z-(?:\d+|auto)\b/g,
        /\.(?:top|right|bottom|left|inset)-(?:\d+|px|auto|full|\d+\.\d+|1\/2|1\/3|2\/3|1\/4|3\/4)\b/g,
        /\.cursor-(?:auto|default|pointer|wait|text|move|help|not-allowed)\b/g,
        /\.select-(?:none|text|all|auto)\b/g,
        /\.transition(?:-(?:none|all|colors|opacity|shadow|transform))?\b/g,
        /\.duration-(?:\d+)\b/g,
        /\.ease-(?:linear|in|out|in-out)\b/g,
        /\.transform\b/g,
        /\.(?:translate-x|translate-y)-(?:\d+|px|full|1\/2|1\/3|2\/3|1\/4|3\/4|\d+\.\d+)\b/g,
        /\.(?:rotate|skew-x|skew-y)-(?:\d+)\b/g,
        /\.scale(?:-x|-y)?-(?:\d+|\d+\.\d+)\b/g,
        /\.overflow-(?:auto|hidden|clip|visible|scroll|x-auto|y-auto|x-hidden|y-hidden|x-clip|y-clip|x-visible|y-visible|x-scroll|y-scroll)\b/g,
    ];
    
    patterns.forEach(pattern => {
        const matches = cssContent.match(pattern) || [];
        matches.forEach(match => {
            // Remove the leading dot and add to set
            const className = match.substring(1);
            utilities.add(className);
        });
    });
    
    // Filter out custom Statamic classes and components
    const excludePatterns = [
        'btn', 'card', 'stack', 'content-', 'cp-', 'ui-', 'bg-architectural-lines',
        'bg-checkerboard', 'mask-bg', 'prose', 'not-prose', 'container', 'sr-only',
        'pointer-events-', 'collapse', 'invisible', 'visible-'
    ];
    
    return Array.from(utilities)
        .filter(className => !excludePatterns.some(pattern => className.startsWith(pattern)))
        .sort();
}

export default defineConfig(({ mode, command }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const isRunningBuild = command === 'build';
    const isProdBuild = isRunningBuild && mode === 'production';
    const isProdDevBuild = isRunningBuild && mode === 'development';

    return {
        base: './',
        plugins: [
            tailwindcss(),
            laravel({
                valetTls: env.VALET_TLS,
                input: ['resources/css/app.css', 'resources/js/index.js'],
                refresh: true,
                publicDirectory: isProdDevBuild ? 'resources/dist-dev' : 'resources/dist',
                hotFile: 'resources/dist/hot',
            }),
            vue(),
            svgLoader(),
            // Generate Tailwind exclusions for addon developers
            generateTailwindExclusions(isProdBuild),
        ],
        css: {
            devSourcemap: true,
        },
        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm-bundler.js',
                '@': path.resolve(__dirname, 'resources/js'),
            },
        },
        build: {
            rollupOptions: {
                output: {
                    plugins: [visualizer({ filename: 'bundle-stats.html' })]
                },
            },
            minify: isProdBuild
        },
        test: { environment: 'jsdom', setupFiles: 'resources/js/tests/setup.js' },
        define: {
            __VUE_PROD_DEVTOOLS__: isProdDevBuild,
        }
    };
});
