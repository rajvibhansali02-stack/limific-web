import sys
import os

filepath = 'index.html'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

target = '''            <a href="index.html" class="nav-brand">
                <span class="brand-insignia">L</span>
                <span>LUMIFIC</span>
            </a>'''

if target in content:
    new_content = content.replace(target, '')
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Success")
else:
    print("Target not found")
