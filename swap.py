import os
import re

files = ['ceiling.html', 'smart-lighting.html', 'wall-sculptures.html', 'ceiling-masterpieces.html']

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    match_details = re.search(r'(<section class="details"[^>]*>.*?</section>\s*)', content, re.DOTALL)
    match_related = re.search(r'(<section class="related-products">.*?</section>\s*)', content, re.DOTALL)
    
    if match_details and match_related:
        details_str = match_details.group(1)
        related_str = match_related.group(1)
        
        # Remove both from content
        content = content.replace(details_str, '')
        content = content.replace(related_str, '')
        
        # Adjust padding so gallery doesn't have 150px bottom padding, but details does.
        # Original details had padding-bottom: 50px, now it needs 150px
        details_str = details_str.replace('padding-bottom: 50px', 'padding-bottom: 150px')
        
        # The related-products gallery in CSS currently has `padding: 0 8vw 150px;`
        # We can just leave it or let CSS handle it. Actually, if we put gallery before details,
        # the gallery's 150px padding will create a huge gap. Let's fix that in CSS instead!
        
        # Insert related THEN details
        new_insert = related_str + details_str
        content = content.replace('<footer>', new_insert + '    <footer>')
        
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f'Successfully updated {file}')
    else:
        print(f'Failed to find sections in {file}')
