import re

file_path = 'shop.html'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# This script restructures the product-info section
# Current: <div class="product-info"><div class="product-details-left">CAT, TITLE, PRICE</div> QTY_WRAPPER </div>
# New: <div class="product-info">
#        <div class="product-details-top">CAT, TITLE</div>
#        <div class="product-details-bottom">PRICE, QTY_WRAPPER</div>
#      </div>

def restructure_info(match):
    info_start = match.group(1)
    # Inside product-details-left
    cat_tag = match.group(2)
    title_tag = match.group(3)
    price_tag = match.group(4)
    qty_wrapper = match.group(5)
    info_end = match.group(6)
    
    new_html = f'''{info_start}
                        <div class="product-details-top">
                            {cat_tag.strip()}
                            {title_tag.strip()}
                        </div>
                        <div class="product-details-bottom">
                            {price_tag.strip()}
                            {qty_wrapper.strip()}
                        </div>
                    {info_end}'''
    return new_html

pattern = re.compile(r'(<div class="product-info">)\s*<div class="product-details-left">(<span class="product-cat-tag">.*?</span>)\s*(<h2 class="product-title">.*?</h2>)\s*(<p class="product-price-tag">.*?</p>)</div>\s*(<div class="card-qty-wrapper">.*?</div>)\s*(</div>)', re.DOTALL)

new_content = pattern.sub(restructure_info, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(new_content)
