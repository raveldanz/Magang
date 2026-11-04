import os
import numpy as np
from PIL import Image

# Open the raw crop of logos
im = Image.open("public/test-artifacts/logos_crop_raw.png").convert("RGBA")
arr = np.array(im, dtype=np.float32)

# Compute distance from white (255, 255, 255)
# arr[..., :3] is RGB
diff = 255.0 - arr[..., :3]
dist = np.sqrt(np.sum(diff**2, axis=-1))

# Transparency curve:
# dist < 12: alpha = 0 (completely transparent)
# dist between 12 and 50: smooth ramp from 0 to 255
# dist >= 50: alpha = 255 (fully opaque)
alpha = np.zeros_like(dist)
mask_mid = (dist >= 12) & (dist < 50)
alpha[mask_mid] = ((dist[mask_mid] - 12) / 38.0) * 255.0
alpha[dist >= 50] = 255.0

# Apply alpha
arr[..., 3] = alpha

# For semi-transparent pixels, normalize RGB so they don't look white-fringed
# When rendered against white or any background, un-premultiply:
# If dist > 12, the color was mixed with white. Let's recover the original color:
norm_alpha = alpha / 255.0
for c in range(3):
    channel = arr[..., c]
    # where alpha is low, avoid divide by zero
    valid = norm_alpha > 0.05
    recovered = 255.0 - (255.0 - channel[valid]) / norm_alpha[valid]
    arr[valid, c] = np.clip(recovered, 0, 255)

out_arr = arr.astype(np.uint8)
strip_img = Image.fromarray(out_arr, mode="RGBA")

# Trim extra transparent space top/bottom/left/right
bbox = strip_img.getbbox()
print("Unified strip bounding box:", bbox)
trimmed_strip = strip_img.crop(bbox)

# Save unified strip
os.makedirs("public/images/certificates", exist_ok=True)
trimmed_strip.save("public/images/certificates/logos_official_strip.png")
print("Saved public/images/certificates/logos_official_strip.png size:", trimmed_strip.size)

# Also extract individual 5 logos
# x coordinates relative to uncropped:
# 1. Surabaya: x=0..82
# 2. Dispusip: x=83..175
# 3. BerAKHLAK: x=176..322
# 4. Bangga: x=323..460
# 5. Zona: x=461..640
ranges = [
    ("logo_surabaya.png", 0, 83),
    ("logo_dispusip.png", 83, 175),
    ("logo_berakhlak.png", 175, 323),
    ("logo_bangga_melayani.png", 323, 460),
    ("logo_zona_integritas.png", 460, 640),
]

for filename, x1, x2 in ranges:
    logo_crop = strip_img.crop((x1, 0, x2, strip_img.height))
    logo_bbox = logo_crop.getbbox()
    if logo_bbox:
        clean_logo = logo_crop.crop(logo_bbox)
        clean_logo.save(f"public/images/certificates/{filename}")
        print(f"Saved {filename}: {clean_logo.size}")

# Also overwrite logo_dispusip.jpg / create webp or png fallback
logo_dispusip = strip_img.crop((83, 0, 175, strip_img.height)).crop(strip_img.crop((83, 0, 175, strip_img.height)).getbbox())
logo_dispusip.save("public/images/certificates/logo_dispusip.png")

