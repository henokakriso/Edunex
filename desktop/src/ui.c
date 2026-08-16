#include "ui.h"

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <locale.h>
#include <stdarg.h>
#include <unistd.h>

static int rows, cols;

void ui_init(void)
{
    setlocale(LC_ALL, "");
    initscr();
    cbreak();
    noecho();
    keypad(stdscr, TRUE);
    curs_set(0);
    start_color();
    use_default_colors();
    init_pair(1, COLOR_CYAN, -1);
    init_pair(2, COLOR_GREEN, -1);
    init_pair(3, COLOR_YELLOW, -1);
    init_pair(4, COLOR_RED, -1);
    init_pair(5, COLOR_MAGENTA, -1);
    init_pair(6, COLOR_BLUE, -1);
    init_pair(7, COLOR_WHITE, -1);
    init_pair(8, COLOR_BLACK, COLOR_CYAN);
    getmaxyx(stdscr, rows, cols);
}

void ui_shutdown(void)
{
    endwin();
}

void ui_clear(void)
{
    clear();
    getmaxyx(stdscr, rows, cols);
}

int ui_cols(void) { return cols; }
int ui_rows(void) { return rows; }

void ui_center(WINDOW *w, int y, const char *fmt, ...)
{
    char buf[512];
    va_list ap;
    va_start(ap, fmt);
    vsnprintf(buf, sizeof buf, fmt, ap);
    va_end(ap);
    int len = (int)strlen(buf);
    int c;
    getmaxyx(w, rows, c);
    int x = (c - len) / 2;
    if (x < 0) x = 0;
    mvwaddstr(w, y, x, buf);
}

void ui_line(WINDOW *w, int y, int col, int max, const char *fmt, ...)
{
    char buf[512];
    va_list ap;
    va_start(ap, fmt);
    vsnprintf(buf, sizeof buf, fmt, ap);
    va_end(ap);
    char *p = buf;
    int n = (int)strlen(p);
    if (n > max) { p[max] = '\0'; n = max; }
    mvwaddnstr(w, y, col, p, n);
}

void ui_banner(WINDOW *w)
{
    ui_center(w, 1, "EDUNEX");
    ui_center(w, 2, "AI-Powered Ethiopian Learning Platform (desktop client)");
    mvwaddch(w, 3, 0, ACS_LTEE);
    for (int i = 1; i < cols - 1; i++)
        mvwaddch(w, 3, i, ACS_HLINE);
    mvwaddch(w, 3, cols - 1, ACS_RTEE);
}

void ui_header(const char *title)
{
    ui_clear();
    ui_banner(stdscr);
    int y = 5;
    attron(COLOR_PAIR(1));
    mvwaddstr(stdscr, y, 0, title);
    attroff(COLOR_PAIR(1));
    for (int i = 0; i < cols; i++)
        mvwaddch(stdscr, y + 1, i, ACS_HLINE);
}

void ui_footer(const char *hint)
{
    attron(A_REVERSE);
    int y = rows - 1;
    mvhline(y, 0, ' ', cols);
    if (hint)
        mvwaddstr(stdscr, y, 1, hint);
    attroff(A_REVERSE);
}

void ui_boxed(int ncols)
{
    int y = rows - 2;
    mvwaddch(stdscr, y, 0, ACS_LTEE);
    for (int i = 1; i < ncols - 1; i++)
        mvwaddch(stdscr, y, i, ACS_HLINE);
    mvwaddch(stdscr, y, ncols - 1, ACS_RTEE);
}

int ui_confirm(const char *msg)
{
    ui_footer(msg);
    ui_center(stdscr, rows - 2, "[Y] yes   [N] no");
    refresh();
    for (;;) {
        int c = getch();
        if (c == 'y' || c == 'Y') return 1;
        if (c == 'n' || c == 'N' || c == 27) return 0;
    }
}

void ui_pause(void)
{
    ui_footer("Press any key to continue...");
    refresh();
    getch();
}

int ui_menu(const char *title, const char **items, int n, int def)
{
    int sel = def;
    for (;;) {
        ui_clear();
        ui_banner(stdscr);
        attron(COLOR_PAIR(1));
        mvwaddstr(stdscr, 5, 0, title);
        attroff(COLOR_PAIR(1));
        int y = 7;
        for (int i = 0; i < n; i++) {
            if (i == sel) {
                attron(A_REVERSE);
                mvaddstr(y, 2, items[i]);
                mvaddstr(y, cols - 8, "<");
                attroff(A_REVERSE);
            } else {
                mvaddstr(y, 4, items[i]);
            }
            y++;
        }
        ui_footer("Up/Down or j/k to move, Enter to select, Esc to go back");
        refresh();
        int c = getch();
        if (c == KEY_UP || c == 'k') { if (sel > 0) sel--; }
        else if (c == KEY_DOWN || c == 'j') { if (sel < n - 1) sel++; }
        else if (c == '\n' || c == '\r') return sel;
        else if (c == 27) return -1;
    }
}

char *ui_prompt(const char *label, char *buf, int len, int password)
{
    int y = rows / 2 - 1;
    echo();
    curs_set(1);
    attron(COLOR_PAIR(1));
    mvwaddstr(stdscr, y - 1, 2, label);
    attroff(COLOR_PAIR(1));
    mvhline(y, 2, ' ', cols - 4);
    mvwaddnstr(stdscr, y, 2, buf, cols - 4);
    move(y, 2);
    if (password) {
        noecho();
        int i = 0;
        int c;
        while (i < len - 1) {
            c = getch();
            if (c == '\n' || c == '\r') break;
            if (c == 127 || c == KEY_BACKSPACE) { if (i > 0) { i--; mvwaddch(stdscr, y, 2 + i, ' '); } continue; }
            buf[i++] = (char)c;
            mvwaddch(stdscr, y, 2 + i - 1, '*');
        }
        buf[i] = '\0';
    } else {
        wgetnstr(stdscr, buf, len - 1);
    }
    noecho();
    curs_set(0);
    return buf;
}

void ui_msg(const char *msg, int error)
{
    int y = rows / 2;
    int len = (int)strlen(msg);
    int x = (cols - len) / 2;
    if (x < 0) x = 0;
    attron(COLOR_PAIR(error ? 4 : 2));
    mvaddstr(y, x, msg);
    attroff(COLOR_PAIR(error ? 4 : 2));
    refresh();
}
