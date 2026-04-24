import sys
import os

filepath = 'shop.html'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

target = '<div class="nav-container">'
replacement = '''<div class="nav-container">
            <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
                <span class="line"></span>
                <span class="line"></span>
            </button>'''

if target in content:
    new_content = content.replace(target, replacement)
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Success")
else:
    print("Target not found")
