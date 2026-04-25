import re
import os

file_path = 'shop.html'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Pattern to find each product card and restructure it
# We look for the image wrap, capture its contents (excluding the qty wrapper),
# then capture the qty wrapper, then the info section.
pattern = re.compile(r'(<div class="product-img-wrap">)(.*?)(<div class="card-qty-wrapper">.*?</div>)(\s*</div>\s*)(<div class="product-info">)(.*?)(</div>)', re.DOTALL)

def restructure(match):
    img_start = match.group(1)
    img_content = match.group(2)
    qty_wrapper = match.group(3)
    img_end = match.group(4)
    info_start = match.group(5)
    info_content = match.group(6)
    info_end = match.group(7)
    
    # New structure: 
    # Image wrap without qty
    # Info starts, wrap text in details-left, then qty-wrapper
    new_html = f'{img_start}{img_content}{img_end}{info_start}\n                        <div class="product-details-left">{info_content.strip()}</div>\n                        {qty_wrapper}\n                    {info_end}'
    return new_html

new_content = pattern.sub(restructure, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(new_content)
