#ifndef EDUNEX_UI_H
#define EDUNEX_UI_H

#include <ncursesw/curses.h>

void ui_init(void);
void ui_shutdown(void);
void ui_banner(WINDOW *w);
void ui_header(const char *title);
void ui_footer(const char *hint);
void ui_center(WINDOW *w, int y, const char *fmt, ...);
void ui_boxed(int ncols);
void ui_line(WINDOW *w, int y, int col, int max, const char *fmt, ...);
int  ui_confirm(const char *msg);
void ui_pause(void);
int  ui_menu(const char *title, const char **items, int n, int def);
char *ui_prompt(const char *label, char *buf, int len, int password);
void ui_msg(const char *msg, int error);
void ui_clear(void);
int ui_cols(void);
int ui_rows(void);

#endif
