#!/usr/bin/env node

/**
 * Complete build script for @statamic/ui package
 * 
 * This script:
 * 1. Auto-generates component exports from ui components
 * 2. Copies and processes the UI CSS
 * 3. Builds the standalone package
 * 4. Validates the output
 */

import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '..');
const uiPackageDir = path.resolve(rootDir, 'resources/js/ui-package');

console.log('🚀 Building @statamic/ui package...\n');

try {
    // Step 1: Generate exports (if the script exists)
    const generateExportsScript = path.resolve(uiPackageDir, 'generate-exports.js');
    if (fs.existsSync(generateExportsScript)) {
        console.log('📝 Generating component exports...');
        execSync(`node "${generateExportsScript}"`, { stdio: 'inherit' });
    }

    // Step 2: Copy and process UI CSS
    console.log('🎨 Processing UI styles...');
    const sourceCss = path.resolve(rootDir, 'resources/css/ui.css');
    const targetCss = path.resolve(uiPackageDir, 'ui-source.css');
    
    if (fs.existsSync(sourceCss)) {
        // Copy the original ui.css as reference
        fs.copyFileSync(sourceCss, targetCss);
        console.log('   ✅ UI CSS copied for reference');
    }

    // Step 3: Build the package
    console.log('🔨 Building standalone package...');
    process.chdir(uiPackageDir);
    execSync('npm run build', { stdio: 'inherit' });

    // Step 4: Validate the output
    console.log('🔍 Validating build output...');
    const distDir = path.resolve(uiPackageDir, 'dist');
    
    const requiredFiles = ['index.js', 'index.cjs', 'ui.css'];
    let allFilesExist = true;
    
    for (const file of requiredFiles) {
        const filePath = path.resolve(distDir, file);
        if (fs.existsSync(filePath)) {
            const stats = fs.statSync(filePath);
            console.log(`   ✅ ${file} (${(stats.size / 1024).toFixed(1)}kb)`);
        } else {
            console.log(`   ❌ ${file} - MISSING`);
            allFilesExist = false;
        }
    }

    if (allFilesExist) {
        console.log('\n🎉 @statamic/ui package built successfully!');
        console.log(`📦 Package ready at: ${distDir}`);
        console.log('\n📖 Usage:');
        console.log('   import { Button, Card } from "@statamic/ui"');
        console.log('   import "@statamic/ui/style.css"');
    } else {
        console.log('\n❌ Build completed but some files are missing');
        process.exit(1);
    }

} catch (error) {
    console.error('\n❌ Build failed:', error.message);
    process.exit(1);
}