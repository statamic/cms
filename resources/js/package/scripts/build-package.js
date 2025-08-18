#!/usr/bin/env node

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const packageRoot = path.resolve(__dirname, '..');
const distDir = path.resolve(packageRoot, '../../dist-package');
const sourceUiFile = path.resolve(__dirname, '../../components/ui/index.js');

console.log('🔨 Building @statamic/cms package...');

// Clean and recreate dist directory
if (fs.existsSync(distDir)) {
    fs.rmSync(distDir, { recursive: true });
}
fs.mkdirSync(distDir, { recursive: true });

console.log('📁 Created clean dist directory');

// 1. Generate UI exports (only dynamic part)
console.log('🎨 Generating UI component exports...');

// Read the source UI file
const sourceContent = fs.readFileSync(sourceUiFile, 'utf8');

// Extract component names from import statements
const importMatches = sourceContent.match(/import\s*\{\s*default\s+as\s+(\w+)\s*\}/g);
const componentNames = [];

if (importMatches) {
    importMatches.forEach(match => {
        const nameMatch = match.match(/as\s+(\w+)/);
        if (nameMatch) {
            componentNames.push(nameMatch[1]);
        }
    });
}

// Also extract from the export block
const exportMatch = sourceContent.match(/export\s*\{([^}]+)\}/s);
if (exportMatch) {
    const exportContent = exportMatch[1];
    const additionalNames = exportContent
        .split(',')
        .map(name => name.trim())
        .filter(name => name && name !== 'publishContextKey' && name !== 'injectPublishContext')
        .map(name => name.replace(/,$/, ''));

    // Add unique names
    additionalNames.forEach(name => {
        if (!componentNames.includes(name)) {
            componentNames.push(name);
        }
    });
}

// Add the special exports manually since they have different patterns
if (!componentNames.includes('publishContextKey')) {
    componentNames.push('publishContextKey');
}
if (!componentNames.includes('injectPublishContext')) {
    componentNames.push('injectPublishContext');
}

// Sort for consistency
componentNames.sort();

console.log(`   Found ${componentNames.length} UI components`);

// Generate ui.js (the only dynamic file)
const uiContent = `
// Runtime UI exports - provided by window.StatamicCms.ui
const ui = (typeof window !== 'undefined' && window.StatamicCms?.ui) || {};
const createProxy = () => new Proxy({}, { get: () => createProxy(), set: () => true, has: () => true });

// Individual component exports
${componentNames.map(name => `export const ${name} = ui.${name} || createProxy();`).join('\n')}
`;

fs.writeFileSync(path.join(distDir, 'ui.js'), uiContent);

// 2. Copy all static files
console.log('📄 Copying static files...');

const staticFiles = [
    { src: 'index.dist.js', dest: 'index.js' },
    { src: 'package.json', dest: 'package.json' },
    { src: 'vite-plugin.js', dest: 'vite-plugin.js' },
    { src: 'index.d.ts', dest: 'index.d.ts' },
    { src: 'ui.d.ts', dest: 'ui.d.ts' }
];

staticFiles.forEach(({ src, dest }) => {
    const sourcePath = path.join(packageRoot, src);
    const destPath = path.join(distDir, dest);
    if (fs.existsSync(sourcePath)) {
        fs.copyFileSync(sourcePath, destPath);
        console.log(`   Copied ${src} → ${dest}`);
    } else {
        console.log(`   ⚠️  Warning: ${src} not found`);
    }
});

console.log('✅ Package build complete!');
console.log(`📦 Generated files in ${distDir}:`);
console.log('   - index.js (main package exports)');
console.log('   - ui.js (UI component exports)');
console.log('   - vite-plugin.js (Vite plugin)');
console.log('   - package.json (package manifest)');
console.log('   - *.d.ts (TypeScript definitions)');
console.log(`🎨 Exported ${componentNames.length} UI components`);
