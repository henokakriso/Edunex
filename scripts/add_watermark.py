#!/usr/bin/env python3
"""Add images to every page of a PDF.

Usage:
  add_watermark.py <input.pdf> <output.pdf> <image_path> [scale_pct]
  add_watermark.py <input.pdf> <output.pdf> --header-left <flag> --header-right <logo> [--center <image>]

Modes:
  Legacy:  single centered watermark image
  Header:  flag on top-left, ministry logo on top-right, optional center watermark
"""
import sys
import pymupdf

def parse_args():
    args = sys.argv[1:]
    if len(args) < 3:
        print("Usage: add_watermark.py <input> <output> <image> [scale]", file=sys.stderr)
        sys.exit(1)
    result = {'input': args[0], 'output': args[1]}
    i = 2
    if args[2] == '--header-left':
        # Header mode
        result['mode'] = 'header'
        while i < len(args):
            if args[i] == '--header-left' and i + 1 < len(args):
                result['header_left'] = args[i + 1]; i += 2
            elif args[i] == '--header-right' and i + 1 < len(args):
                result['header_right'] = args[i + 1]; i += 2
            elif args[i] == '--center' and i + 1 < len(args):
                result['center'] = args[i + 1]; i += 2
            elif args[i] == '--scale' and i + 1 < len(args):
                result['center_scale'] = float(args[i + 1]); i += 2
            else:
                i += 1
    else:
        result['mode'] = 'legacy'
        result['image'] = args[2]
        result['scale'] = float(args[3]) if len(args) > 3 else 30.0
    return result

def add_legacy(doc, image_path, scale_pct):
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

def add_header(doc, left_path=None, right_path=None, center_path=None, center_scale=30):
    for page in doc:
        rect = page.rect
        W = rect.width
        H = rect.height

        # Flag — top-left corner (like admin PDF: 14mm from left, 4mm from top)
        if left_path:
            # ~36pt wide, 28pt tall (matches admin layout flag size)
            flag_w = 36
            flag_h = 28
            flag_x = 14
            flag_y = 6
            page.insert_image(
                pymupdf.Rect(flag_x, flag_y, flag_x + flag_w, flag_y + flag_h),
                filename=left_path,
                overlay=False,
                alpha=255
            )

        # Ministry logo — top-right corner
        if right_path:
            logo_w = 36
            logo_h = 36
            logo_x = W - 14 - logo_w
            logo_y = 4
            page.insert_image(
                pymupdf.Rect(logo_x, logo_y, logo_x + logo_w, logo_y + logo_h),
                filename=right_path,
                overlay=False,
                alpha=255
            )

        # Center watermark (Edunex logo, faded)
        if center_path:
            img_w = W * (center_scale / 100.0)
            img_h = img_w
            x = (W - img_w) / 2
            y = (H - img_h) / 2
            page.insert_image(
                pymupdf.Rect(x, y, x + img_w, y + img_h),
                filename=center_path,
                overlay=False,
                alpha=40
            )

def main():
    cfg = parse_args()
    doc = pymupdf.open(cfg['input'])

    if cfg['mode'] == 'legacy':
        add_legacy(doc, cfg['image'], cfg['scale'])
    else:
        add_header(
            doc,
            left_path=cfg.get('header_left'),
            right_path=cfg.get('header_right'),
            center_path=cfg.get('center'),
            center_scale=cfg.get('center_scale', 30)
        )

    doc.save(cfg['output'])
    doc.close()
    print("OK")

if __name__ == '__main__':
    main()
