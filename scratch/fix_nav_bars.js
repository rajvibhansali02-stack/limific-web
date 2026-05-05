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

    // Fix the broken replacement from previous step if any
    if (content.includes('</li>n                <li>')) {
        content = content.replace(/<\/li>n                <li>/g, '</li>\n                <li>');
        changed = true;
    }

    // Standard nav update for HTML files (static LOGIN link)
    if (file.endsWith('.html')) {
        if (content.includes('data-value="CONTACT"><span>CONTACT</span><span>CONTACT</span></a></li>') && !content.includes('data-value="LOGIN"')) {
            content = content.replace(
                'data-value="CONTACT"><span>CONTACT</span><span>CONTACT</span></a></li>',
                'data-value="CONTACT"><span>CONTACT</span><span>CONTACT</span></a></li>\n                <li><a href="login.php" class="glitch-link" data-value="LOGIN"><span>LOGIN</span><span>LOGIN</span></a></li>'
            );
            changed = true;
        }
    }

    if (changed) {
        console.log(`Updated ${file}`);
        fs.writeFileSync(file, content, 'utf8');
    }
});
