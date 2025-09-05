#!/usr/bin/env node

/**
 * Auto-generates the index.js file by scanning the components/ui directory
 * This ensures the exports are always in sync with the actual components
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const uiComponentsPath = path.resolve(__dirname, '../components/ui');
const indexPath = path.resolve(__dirname, 'index.js');

// Read the existing ui/index.js to get the export names and import paths
const uiIndexPath = path.resolve(uiComponentsPath, 'index.js');

try {
    const uiIndexContent = fs.readFileSync(uiIndexPath, 'utf8');
    
    // Extract imports and exports from the existing ui/index.js
    const importLines = [];
    const exportNames = [];
    
    const lines = uiIndexContent.split('\n');
    let inImportSection = true;
    let inExportSection = false;
    
    for (const line of lines) {
        const trimmedLine = line.trim();
        
        if (trimmedLine.startsWith('import ')) {
            // Convert the import path to relative path for standalone package
            const relativePath = line.replace('from "./', 'from "../components/ui/');
            importLines.push(relativePath);
        }
        
        if (trimmedLine.startsWith('export {')) {
            inExportSection = true;
            continue;
        }
        
        if (inExportSection) {
            if (trimmedLine === '};') {
                break;
            }
            
            // Extract export name (remove trailing comma)
            const exportName = trimmedLine.replace(',', '').trim();
            if (exportName) {
                exportNames.push(exportName);
            }
        }
    }
    
    // Generate the new index.js content
    const content = `// Auto-generated exports from ../components/ui/
// This file exports all UI components for standalone use

// Import all components from the ui components directory
${importLines.join('\n')}

// Export all components
export {
    ${exportNames.join(',\n    ')},
};`;

    // Write the generated content to index.js
    fs.writeFileSync(indexPath, content, 'utf8');
    
    console.log('✅ Successfully generated index.js with', exportNames.length, 'exports');
    
} catch (error) {
    console.error('❌ Error generating exports:', error.message);
    process.exit(1);
}