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

    // Use regex to find duplicate login links and keep only one
    // We look for <li>...login.php...</li> repeated
    const loginPattern = /<li><a href="login\.php"[\s\S]+?<\/li>/g;
    const matches = content.match(loginPattern);
    
    if (matches && matches.length > 1) {
        console.log(`Found ${matches.length} login links in ${file}. Cleaning up...`);
        // Replace all occurrences with the first one
        const uniqueLogin = matches[0];
        // This is a bit tricky, let's just replace the whole block
        let newContent = content;
        for(let i=1; i<matches.length; i++) {
            newContent = newContent.replace(matches[i], '');
        }
        // Cleanup potential extra newlines
        newContent = newContent.replace(/\n\s*\n\s*<\/ul>/g, '\n            </ul>');
        
        if (newContent !== content) {
            content = newContent;
            changed = true;
        }
    }

    if (changed) {
        console.log(`Cleaned up ${file}`);
        fs.writeFileSync(file, content, 'utf8');
    }
});
