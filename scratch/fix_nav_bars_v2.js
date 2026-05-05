const fs = require('fs');
const path = require('path');

const walk = (dir) => {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach((file) => {
        const fullPath = path.resolve(dir, file);
        const stat = fs.statSync(fullPath);
        if (stat && stat.isDirectory()) {
            if (file !== 'node_modules' && file !== '.git' && file !== 'scratch') {
                results = results.concat(walk(fullPath));
            }
        } else {
            const ext = path.extname(fullPath).toLowerCase();
            if (['.html', '.php'].includes(ext)) {
                results.push(fullPath);
            }
        }
    });
    return results;
};

const files = walk('.');

files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    let changed = false;

    // Fix the broken replacement from previous step
    // It seems it might be `n or \n or literal n
    if (content.includes('</li>`n')) {
        content = content.replace(/<\/li>`n\s+<li>/g, '</li>\n                <li>');
        changed = true;
    }
    
    // Also check for the literal n if it was just n
    if (content.includes('</li>n                <li>')) {
        content = content.replace(/<\/li>n\s+<li>/g, '</li>\n                <li>');
        changed = true;
    }

    if (changed) {
        console.log(`Updated ${file}`);
        fs.writeFileSync(file, content, 'utf8');
    }
});
