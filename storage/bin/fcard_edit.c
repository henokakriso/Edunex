/* ============================================================
 * EDUNEX flashcard image editor (C)
 *
 * Usage: fcard_edit <input-image> <text-file> <output.png> [--band top|bottom] [--size N]
 *
 * Reads a picture (PNG/JPEG/WebP), stamps the question/answer text
 * onto it with a translucent band and a built-in 5x7 bitmap font,
 * and writes a PNG the student can preview/download.
 *
 * Exit codes: 0 = ok, 1 = error (stderr), 2 = usage.
 * ============================================================ */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdint.h>
#include <ctype.h>

#include <png.h>
#include <jpeglib.h>
#include <webp/decode.h>
#include <webp/encode.h>

/* ---- 5x7 bitmap font (public domain), chars 32..126 ---- */
static const char *FONT[95] = {
 "0",      "10111",  "00001010","10010100","10000001","10110101","10001000","101",    "10000001","10000001","0",      "1000",   "10000000","1000",    "0",
 "01111000","00000001","11111000","11111000","00011000","11111000","11111000","11111000","00000001","11111000","11111000","0",      "0",      "10000000","00000000","00000000",
 "00000000","01111000","11001000","11111000","10001000","11111000","01111000","10000000","00000000","11110000","10011000","10000000","10000100","10000000","10000000","10000000",
 "11111000","10001000","11111000","10001000","11111000","10001000","11111000","00000000","00000000","00000000","10000000","00101000","10100000","10000000","00000000","01111000",
 "11111000","10001000","11111000","10001000","11111000","00000000","11111000","00000000","11110000","00000000","00000000","00000000","11111000","00000000","11111000","10001000",
 "00000000","11111000","10000000","11111000","11111000","00000000","10001000","01111000","01111000","11111000","01111000","11111000","11111000","01111000","01111000","01111000",
};

static unsigned char *load_png(const char *path, int *w, int *h, int *has_alpha);
static unsigned char *load_jpeg(const char *path, int *w, int *h);
static unsigned char *load_webp(const char *path, int *w, int *h);
static int save_png(const char *path, const unsigned char *rgb, int w, int h);
static size_t read_file(const char *path, char **out);

static int glyph_width(char c) {
    if (c < 32 || c > 126) return 0;
    return 5;
}

static const char *glyph(char c) {
    return FONT[c - 32];
}

/* measure wrapped lines, returns number of lines */
static char **wrap_text(const char *text, int max_chars, int *nlines, size_t words_len);

int main(int argc, char **argv) {
    if (argc < 4 || argc > 7) {
        fprintf(stderr, "usage: %s <input> <text-file> <output.png> [--band top|bottom] [--size N]\n", argv[0]);
        return 2;
    }
    const char *in = argv[1], *tfile = argv[2], *out = argv[3];
    int band = 0;            /* 0 = bottom, 1 = top */
    int px = 1;              /* scale factor for font */
    for (int i = 4; i < argc; i++) {
        if (!strcmp(argv[i], "--band") && i + 1 < argc) band = !strcmp(argv[i + 1], "top") ? 1 : 0;
        else if (!strcmp(argv[i], "--size") && i + 1 < argc) { int v = atoi(argv[i + 1]); if (v >= 1 && v <= 8) px = v; }
    }

    char *text = NULL;
    size_t rlen = read_file(tfile, &text);
    if (rlen == (size_t)-1 || !text) {
        fprintf(stderr, "cannot read text file '%s' (rc=%zu)\n", tfile, rlen);
        return 1;
    }
    /* strip trailing newline */
    size_t tl = strlen(text);
    while (tl > 0 && (text[tl - 1] == '\n' || text[tl - 1] == '\r' || text[tl - 1] == ' ')) text[--tl] = '\0';

    int w = 0, h = 0, has_alpha = 0;
    unsigned char *rgb = NULL;
    const char *ext = strrchr(in, '.');
    if (ext) {
        if (!strcasecmp(ext, ".png")) rgb = load_png(in, &w, &h, &has_alpha);
        else if (!strcasecmp(ext, ".jpg") || !strcasecmp(ext, ".jpeg")) rgb = load_jpeg(in, &w, &h);
        else if (!strcasecmp(ext, ".webp")) rgb = load_webp(in, &w, &h);
    }
    if (!rgb) { fprintf(stderr, "cannot decode %s (ext=%s, w=%d h=%d)\n", in, ext ? ext : "NULL", w, h); free(text); return 1; }
    if (has_alpha) { /* flatten onto white */ }

    /* font metrics */
    const int fw = 5 * px, fh = 7 * px, spacing = 1 * px;

    /* wrap text to fit width */
    int max_chars = (w - 24) / (fw + spacing);
    if (max_chars < 8) max_chars = 8;
    char **lines = NULL;
    int nlines = 0;
    {
        /* simple greedy wrap */
        char *copy = strdup(text);
        int alloc = 8, cap = alloc;
        lines = malloc(sizeof(char *) * alloc);
        char *tok = strtok(copy, " ");
        char line[1024] = "";
        while (tok) {
            if ((int)strlen(line) + (int)strlen(tok) + 1 > max_chars) {
                if (nlines == cap) { cap *= 2; lines = realloc(lines, sizeof(char *) * cap); }
                lines[nlines++] = strdup(line[0] ? line : tok);
                line[0] = '\0';
                if (line[0] == '\0' && (int)strlen(tok) > max_chars) {
                    /* hard wrap long word */
                    char tmp[1024]; snprintf(tmp, sizeof(tmp), "%.*s", max_chars, tok);
                    if (nlines == cap) { cap *= 2; lines = realloc(lines, sizeof(char *) * cap); }
                    lines[nlines++] = strdup(tmp);
                    tok += max_chars;
                    continue;
                }
            }
            if (line[0]) strcat(line, " ");
            strcat(line, tok);
            tok = strtok(NULL, " ");
        }
        if (line[0]) { if (nlines == cap) lines = realloc(lines, sizeof(char *) * (cap + 1)); lines[nlines++] = strdup(line); }
        free(copy);
    }

    int band_h = nlines * (fh + spacing) + 12;
    if (band_h > h / 2) band_h = h / 2;

    int y0 = band ? 0 : h - band_h;
    /* draw band: dark overlay */
    for (int y = y0; y < y0 + band_h; y++) {
        for (int x = 0; x < w; x++) {
            unsigned char *p = rgb + (y * w + x) * 3;
            p[0] = (unsigned char)(p[0] * 0.55);
            p[1] = (unsigned char)(p[1] * 0.55);
            p[2] = (unsigned char)(p[2] * 0.55);
        }
    }

    /* draw text lines centered */
    int text_w = 0;
    for (int i = 0; i < nlines; i++) {
        int lw = (int)strlen(lines[i]) * (fw + spacing) - spacing;
        if (lw > text_w) text_w = lw;
    }
    int x0 = (w - text_w) / 2;
    int ty = y0 + 6;
    for (int i = 0; i < nlines; i++) {
        int lw = (int)strlen(lines[i]) * (fw + spacing) - spacing;
        int cx = x0 + (text_w - lw) / 2;
        const char *s = lines[i];
        for (size_t k = 0; s[k]; k++) {
            const char *g = glyph(s[k]);
            if (!g) continue;
            for (int gy = 0; gy < 7; gy++) {
                char bits = g[gy];
                for (int gx = 0; gx < 5; gx++) {
                    if (bits & (1 << (4 - gx))) {
                        for (int sy = 0; sy < px; sy++)
                            for (int sx = 0; sx < px; sx++) {
                                int xx = cx + k * (fw + spacing) + gx * px + sx;
                                int yy = ty + i * (fh + spacing) + gy * px + sy;
                                if (xx < w && yy < h) {
                                    unsigned char *p = rgb + (yy * w + xx) * 3;
                                    p[0] = 255; p[1] = 255; p[2] = 255;
                                }
                            }
                    }
                }
            }
        }
    }
    for (int i = 0; i < nlines; i++) free(lines[i]);
    free(lines);

    int rc = save_png(out, rgb, w, h);
    free(rgb);
    free(text);
    if (rc != 0) { fprintf(stderr, "cannot write %s\n", out); return 1; }
    return 0;
}

/* ---- PNG ---- */
static unsigned char *load_png(const char *path, int *w, int *h, int *has_alpha) {
    FILE *f = fopen(path, "rb");
    if (!f) return NULL;
    unsigned char sig[8];
    if (fread(sig, 1, 8, f) != 8 || png_sig_cmp(sig, 0, 8)) { fclose(f); return NULL; }
    png_structp png = png_create_read_struct(PNG_LIBPNG_VER_STRING, NULL, NULL, NULL);
    png_infop info = png_create_info_struct(png);
    if (!png || !info) { fclose(f); return NULL; }
    if (setjmp(png_jmpbuf(png))) { png_destroy_read_struct(&png, &info, NULL); fclose(f); return NULL; }
    png_init_io(png, f);
    png_set_sig_bytes(png, 8);
    png_read_info(png, info);
    int ww = png_get_image_width(png, info), hh = png_get_image_height(png, info);
    int ct = png_get_color_type(png, info);
    if (ct == PNG_COLOR_TYPE_PALETTE) png_set_palette_to_rgb(png);
    if (ct == PNG_COLOR_TYPE_GRAY) png_set_gray_to_rgb(png);
    if (png_get_bit_depth(png, info) < 8) png_set_expand(png);
    *has_alpha = (ct & PNG_COLOR_MASK_ALPHA) || ct == PNG_COLOR_TYPE_PALETTE;
    if (*has_alpha) png_set_strip_16(png); else png_set_strip_16(png);
    png_read_update_info(png, info);
    png_bytep *rows = malloc(sizeof(png_bytep) * hh);
    unsigned char *rgb = calloc((size_t)ww * hh * 3, 1);
    for (int y = 0; y < hh; y++) rows[y] = malloc(png_get_rowbytes(png, info));
    png_read_image(png, rows);
    int channels = png_get_channels(png, info);
    for (int y = 0; y < hh; y++) {
        for (int x = 0; x < ww; x++) {
            png_bytep px = rows[y] + x * channels;
            unsigned char a = channels == 4 ? px[3] : 255;
            unsigned char *o = rgb + ((size_t)y * ww + x) * 3;
            /* composite over white */
            o[0] = (unsigned char)((px[0] * a + 255 * (255 - a)) / 255);
            o[1] = (unsigned char)((px[1] * a + 255 * (255 - a)) / 255);
            o[2] = (unsigned char)((px[2] * a + 255 * (255 - a)) / 255);
        }
        free(rows[y]);
    }
    free(rows);
    png_destroy_read_struct(&png, &info, NULL);
    fclose(f);
    *w = ww; *h = hh;
    return rgb;
}

/* ---- JPEG ---- */
static unsigned char *load_jpeg(const char *path, int *w, int *h) {
    FILE *f = fopen(path, "rb");
    if (!f) return NULL;
    struct jpeg_decompress_struct cinfo;
    struct jpeg_error_mgr jerr;
    cinfo.err = jpeg_std_error(&jerr);
    jpeg_create_decompress(&cinfo);
    jpeg_stdio_src(&cinfo, f);
    if (jpeg_read_header(&cinfo, TRUE) != JPEG_HEADER_OK) { jpeg_destroy_decompress(&cinfo); fclose(f); return NULL; }
    cinfo.out_color_space = JCS_RGB;
    jpeg_start_decompress(&cinfo);
    int ww = cinfo.output_width, hh = cinfo.output_height;
    unsigned char *rgb = malloc((size_t)ww * hh * 3);
    while (cinfo.output_scanline < hh) {
        unsigned char *row = rgb + (size_t)cinfo.output_scanline * ww * 3;
        jpeg_read_scanlines(&cinfo, &row, 1);
    }
    jpeg_finish_decompress(&cinfo);
    jpeg_destroy_decompress(&cinfo);
    fclose(f);
    *w = ww; *h = hh;
    return rgb;
}

/* ---- WebP ---- */
static unsigned char *load_webp(const char *path, int *w, int *h) {
    char *data = NULL;
    if (read_file(path, &data) != 0) return NULL;
    int ww = 0, hh = 0;
    unsigned char *rgb = WebPDecodeRGB((const uint8_t *)data, strlen(data), &ww, &hh);
    free(data);
    if (!rgb) return NULL;
    *w = ww; *h = hh;
    return rgb;
}

/* ---- PNG writer ---- */
static int save_png(const char *path, const unsigned char *rgb, int w, int h) {
    FILE *f = fopen(path, "wb");
    if (!f) return -1;
    png_structp png = png_create_write_struct(PNG_LIBPNG_VER_STRING, NULL, NULL, NULL);
    png_infop info = png_create_info_struct(png);
    if (!png || !info) { fclose(f); return -1; }
    if (setjmp(png_jmpbuf(png))) { png_destroy_write_struct(&png, &info); fclose(f); return -1; }
    png_init_io(png, f);
    png_set_IHDR(png, info, w, h, 8, PNG_COLOR_TYPE_RGB, PNG_INTERLACE_NONE,
                 PNG_COMPRESSION_TYPE_DEFAULT, PNG_FILTER_TYPE_DEFAULT);
    png_write_info(png, info);
    png_bytep *rows = malloc(sizeof(png_bytep) * h);
    for (int y = 0; y < h; y++) rows[y] = (png_bytep)(rgb + (size_t)y * w * 3);
    png_write_image(png, rows);
    png_write_end(png, NULL);
    png_destroy_write_struct(&png, &info);
    free(rows);
    fclose(f);
    return 0;
}

static size_t read_file(const char *path, char **out) {
    FILE *f = fopen(path, "rb");
    if (!f) return (size_t)-1;
    fseek(f, 0, SEEK_END);
    long n = ftell(f);
    fseek(f, 0, SEEK_SET);
    char *buf = malloc(n + 1);
    if (n > 0 && fread(buf, 1, n, f) != (size_t)n) { fclose(f); free(buf); return (size_t)-1; }
    buf[n] = '\0';
    fclose(f);
    *out = buf;
    return (size_t)n;
}
