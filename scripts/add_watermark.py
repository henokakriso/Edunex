#!/usr/bin/env python3
"""Add a watermark image to every page of a PDF. Usage: python3 add_watermark.py <input.pdf> <output.pdf> <image_path> [scale_pct]"""
import sys
import pymupdf

if len(sys.argv) < 4:
    print("Usage: add_watermark.py <input.pdf> <output.pdf> <image_path> [scale_pct]", file=sys.stderr)
    sys.exit(1)

input_path = sys.argv[1]
output_path = sys.argv[2]
image_path = sys.argv[3]
scale_pct = float(sys.argv[4]) if len(sys.argv) > 4 else 30.0

doc = pymupdf.open(input_path)
for page in doc:
    rect = page.rect
    img_w = rect.width * (scale_pct / 100.0)
    img_h = img_w
    x = (rect.width - img_w) / 2
    y = (rect.height - img_h) / 2
    page.insert_image(
        pymupdf.Rect(x, y, x + img_w, y + img_h),
        filename=image_path,
        overlay=False,
        alpha=128
    )

doc.save(output_path)
doc.close()
print("OK")
