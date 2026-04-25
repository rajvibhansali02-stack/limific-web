import os
from PIL import Image

image_dir = 'images'
files = os.listdir(image_dir)

print(f"Starting conversion in {image_dir}...")

for filename in files:
    if filename.lower().endswith('.png'):
        if filename.lower() == 'logo.png':
            print(f"Skipping {filename} (preserving logo quality)")
            continue
            
        png_path = os.path.join(image_dir, filename)
        webp_filename = os.path.splitext(filename)[0] + '.webp'
        webp_path = os.path.join(image_dir, webp_filename)
        
        try:
            with Image.open(png_path) as img:
                # Convert to RGB if necessary (WebP supports RGBA but some PNGs might be weird)
                # Keep RGBA for transparency
                img.save(webp_path, 'WEBP', quality=80)
                print(f"Converted: {filename} -> {webp_filename}")
                
                # Verify size reduction
                png_size = os.path.getsize(png_path)
                webp_size = os.path.getsize(webp_path)
                reduction = (png_size - webp_size) / png_size * 100
                print(f"  Reduction: {reduction:.1f}% ({png_size/1024:.1f}KB -> {webp_size/1024:.1f}KB)")
                
                # Delete original PNG to clean up
                os.remove(png_path)
                print(f"  Removed original: {filename}")
        except Exception as e:
            print(f"Error converting {filename}: {e}")

print("Conversion complete.")
