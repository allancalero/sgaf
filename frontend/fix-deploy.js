const fs = require('fs');
const path = require('path');

// Configuration
const PUBLIC_DIR = path.resolve(__dirname, '../public/SGAF2');
const BROWSER_DIR = path.join(PUBLIC_DIR, 'browser');

console.log('🔄 Checking for build artifacts in:', BROWSER_DIR);

if (fs.existsSync(BROWSER_DIR)) {
    console.log('📂 Found "browser" subdirectory. Moving files up...');

    const files = fs.readdirSync(BROWSER_DIR);

    files.forEach(file => {
        const srcPath = path.join(BROWSER_DIR, file);
        const destPath = path.join(PUBLIC_DIR, file);

        // Remove destination if it exists (overwrite)
        if (fs.existsSync(destPath)) {
            if (fs.lstatSync(destPath).isDirectory()) {
                fs.rmSync(destPath, { recursive: true, force: true });
            } else {
                fs.unlinkSync(destPath);
            }
        }

        fs.renameSync(srcPath, destPath);
    });

    // Clean up empty browser dir
    fs.rmdirSync(BROWSER_DIR);
    console.log('✅ Deployment path fixed successfully!');
} else {
    console.log('✨ No "browser" subdirectory found. Structure is already correct.');
}
