#include "screens.h"
#include "api.h"
#include "ui.h"

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <json-c/json.h>

static void print_rows(const char *title, json_object *arr,
                       const char *const *keys, int nkeys, int maxrows)
{
    ui_clear();
    ui_banner(stdscr);
    attron(COLOR_PAIR(1));
    mvwaddstr(stdscr, 5, 0, title);
    attroff(COLOR_PAIR(1));
    int y = 7;
    int n = arr ? (int)json_object_array_length(arr) : 0;
    if (n > maxrows) n = maxrows;
    int cw = ui_cols();
    for (int i = 0; i < n; i++) {
        json_object *row = json_object_array_get_idx(arr, i);
        char line[400] = "";
        for (int k = 0; k < nkeys; k++) {
            const char *v = js_str(row, keys[k], "");
            char tmp[200];
            snprintf(tmp, sizeof tmp, "%s%s", k ? "  |  " : "", v);
            strncat(line, tmp, sizeof line - strlen(line) - 1);
        }
        if ((int)strlen(line) > cw - 4) line[cw - 4] = '\0';
        mvaddstr(y++, 2, line);
    }
    if (!n)
        mvaddstr(y++, 2, "(empty)");
    ui_footer("Press any key to continue...");
    getch();
}

/* ---------------------------------------------------------- LOGIN */

int screen_login(void)
{
    ApiResp r;
    char url[256], id[120], pw[120];
    char *body;

    snprintf(url, sizeof url, "%s", g_base_url);
    for (;;) {
        ui_clear();
        ui_banner(stdscr);
        ui_prompt("Server URL (default: http://localhost:8080)", url, sizeof url, 0);
        if (url[0]) api_set_base(url);
        else api_set_base("http://localhost:8080");
        ui_prompt("Student ID or email:", id, sizeof id, 0);
        ui_prompt("Password:", pw, sizeof pw, 1);

        ui_msg("Logging in...", 0);
        size_t blen = strlen(id) + strlen(pw) + 80;
        body = malloc(blen);
        char idj[240] = "", pwj[240] = "";
        size_t ip = 0, pp = 0;
        for (size_t i = 0; id[i] && ip < 230; i++) {
            if (id[i] == '"' || id[i] == '\\') idj[ip++] = '\\';
            idj[ip++] = id[i];
        }
        idj[ip] = '\0';
        for (size_t i = 0; pw[i] && pp < 230; i++) {
            if (pw[i] == '"' || pw[i] == '\\') pwj[pp++] = '\\';
            pwj[pp++] = pw[i];
        }
        pwj[pp] = '\0';
        snprintf(body, blen, "{\"identifier\":\"%s\",\"password\":\"%s\"}", idj, pwj);

        if (api_request("POST", "index.php?r=api/login", body, &r) == 0 &&
            r.json && json_object_get_boolean(json_object_object_get(r.json, "ok"))) {
            json_object *u = json_object_object_get(r.json, "user");
            g_me.id = (int)json_object_get_int64(json_object_object_get(u, "id"));
            strncpy(g_me.first_name, js_str(u, "first_name", ""), sizeof g_me.first_name - 1);
            strncpy(g_me.last_name, js_str(u, "last_name", ""), sizeof g_me.last_name - 1);
            strncpy(g_me.email, js_str(u, "email", ""), sizeof g_me.email - 1);
            strncpy(g_me.role, js_str(u, "role", ""), sizeof g_me.role - 1);
            g_me.xp = (int)json_object_get_int64(json_object_object_get(u, "xp"));
            g_me.level = (int)json_object_get_int64(json_object_object_get(u, "level"));
            g_me.school_id = (int)json_object_get_int64(json_object_object_get(u, "school_id"));
            strncpy(g_me.student_id, js_str(u, "student_id", ""), sizeof g_me.student_id - 1);
            api_set_token(js_str(r.json, "token", ""));
            free(body);
            api_resp_free(&r);
            return 0;
        }
        char msg[512];
        snprintf(msg, sizeof msg, "Login failed: %s", r.json ? api_err(&r) : "network error");
        api_resp_free(&r);
        free(body);
        ui_msg(msg, 1);
        sleep(2);
        if (ui_confirm("Try again?")) continue;
        return 1;
    }
}

/* ------------------------------------------------------- DASHBOARD */

int screen_dashboard(void)
{
    ApiResp r;
    api_request("GET", "index.php?r=api/gamification", NULL, &r);
    ui_clear();
    ui_banner(stdscr);
    attron(COLOR_PAIR(1));
    mvwaddstr(stdscr, 5, 2, "DASHBOARD");
    attroff(COLOR_PAIR(1));
    attron(A_BOLD);
    mvprintw(7, 2, "Welcome, %s %s!", g_me.first_name, g_me.last_name);
    attroff(A_BOLD);
    mvprintw(8, 2, "Role: %s    Student ID: %s", g_me.role, g_me.student_id[0] ? g_me.student_id : "-");

    if (r.json && json_object_get_boolean(json_object_object_get(r.json, "ok"))) {
        json_object *p = json_object_object_get(r.json, "profile");
        mvprintw(10, 2, "XP: %lld    Level: %lld    Streak: %lld days",
                 (long long)json_object_get_int64(json_object_object_get(p, "xp")),
                 (long long)json_object_get_int64(json_object_object_get(p, "level")),
                 (long long)json_object_get_int64(json_object_object_get(p, "streak")));
        mvprintw(11, 2, "Rank: #%lld", (long long)json_object_get_int64(json_object_object_get(r.json, "rank")));
        json_object *badges = json_object_object_get(r.json, "badges");
        int nb = badges ? (int)json_object_array_length(badges) : 0;
        mvprintw(13, 2, "Badges unlocked: %d", nb);
    } else {
        ui_msg("Could not load profile (server down?)", 1);
    }
    api_resp_free(&r);

    if (api_request("GET", "index.php?r=api/calendar", NULL, &r) == 0 && r.json) {
        json_object *evs = json_object_object_get(r.json, "events");
        int n = evs ? (int)json_object_array_length(evs) : 0;
        mvprintw(15, 2, "Upcoming calendar events: %d", n);
        int y = 17;
        for (int i = 0; i < n && i < 8; i++) {
            json_object *e = json_object_array_get_idx(evs, i);
            mvprintw(y++, 2, "  %s  %s", js_str(e, "start_at", ""), js_str(e, "title", ""));
        }
    }
    api_resp_free(&r);
    ui_footer("Press any key to continue...");
    getch();
    return 0;
}

/* --------------------------------------------------- NOTIFICATIONS */

int screen_notifications(void)
{
    ApiResp r;
    if (api_request("GET", "index.php?r=api/notifications/poll", NULL, &r) == 0 && r.json) {
        json_object *items = json_object_object_get(r.json, "items");
        static const char *keys[] = { "title", "body", "created_at" };
        char title[200];
        snprintf(title, sizeof title, "NOTIFICATIONS  (unread: %s)",
                 js_str(r.json, "unread", "0"));
        print_rows(title, items, keys, 3, 20);
    } else {
        ui_msg("Failed to load notifications.", 1);
        sleep(1);
    }
    api_resp_free(&r);
    return 0;
}

/* -------------------------------------------------------- COURSES */

int screen_courses(void)
{
    ApiResp r;
    if (api_request("GET", "index.php?r=api/courses", NULL, &r) == 0 && r.json) {
        json_object *cs = json_object_object_get(r.json, "courses");
        static const char *keys[] = { "title", "code", "status", "students" };
        print_rows("MY COURSES", cs, keys, 4, 25);
    } else {
        ui_msg("Failed to load courses.", 1);
        sleep(1);
    }
    api_resp_free(&r);
    return 0;
}

/* --------------------------------------------------------- GRADES */

int screen_grades(void)
{
    ui_clear();
    ui_banner(stdscr);
    attron(COLOR_PAIR(1));
    mvwaddstr(stdscr, 5, 2, "GRADES");
    attroff(COLOR_PAIR(1));
    mvaddstr(7, 2, "Use the web dashboard (student/grades) for the detailed gradebook.");
    mvprintw(9, 2, "You: level %d, %d XP.", g_me.level, g_me.xp);
    ui_footer("Press any key to continue...");
    getch();
    return 0;
}

/* ----------------------------------------------------- ATTENDANCE */

int screen_attendance(void)
{
    ApiResp r;
    if (api_request("GET", "index.php?r=api/attendance-mobile", NULL, &r) == 0 && r.json) {
        json_object *rows = json_object_object_get(r.json, "records");
        static const char *keys[] = { "date", "status", "course_title" };
        print_rows("MY ATTENDANCE", rows, keys, 3, 25);
    } else {
        ui_msg("Failed to load attendance.", 1);
        sleep(1);
    }
    api_resp_free(&r);
    return 0;
}

/* ------------------------------------------------------- MESSAGES */

int screen_messages(void)
{
    char to[120], body[400];
    ui_clear();
    ui_banner(stdscr);
    attron(COLOR_PAIR(1));
    mvwaddstr(stdscr, 5, 2, "SEND DIRECT MESSAGE");
    attroff(COLOR_PAIR(1));
    to[0] = 0; body[0] = 0;
    ui_prompt("Recipient user ID:", to, sizeof to, 0);
    ui_prompt("Message:", body, sizeof body, 0);
    if (!to[0] || !body[0]) return 0;

    char req[700];
    snprintf(req, sizeof req, "{\"to_id\":%s,\"body\":\"%s\"}", to, body);
    ApiResp r;
    if (api_request("POST", "index.php?r=api/messages/send", req, &r) == 0 &&
        r.json && json_object_get_boolean(json_object_object_get(r.json, "ok")))
        ui_msg("Message sent.", 0);
    else
        ui_msg(r.json ? api_err(&r) : "Network error", 1);
    sleep(2);
    api_resp_free(&r);
    return 0;
}

/* ------------------------------------------------------- CALENDAR */

int screen_calendar(void)
{
    ApiResp r;
    if (api_request("GET", "index.php?r=api/calendar", NULL, &r) == 0 && r.json) {
        json_object *evs = json_object_object_get(r.json, "events");
        static const char *keys[] = { "start_at", "title", "type", "location" };
        print_rows("UPCOMING EVENTS (next 14 days)", evs, keys, 4, 25);
    } else {
        ui_msg("Failed to load calendar.", 1);
        sleep(1);
    }
    api_resp_free(&r);
    return 0;
}

/* ---------------------------------------------------- LEADERBOARD */

int screen_leaderboard(void)
{
    ui_clear();
    ui_banner(stdscr);
    attron(COLOR_PAIR(1));
    mvwaddstr(stdscr, 5, 2, "LEADERBOARD");
    attroff(COLOR_PAIR(1));
    mvaddstr(7, 2, "Top students by XP:");
    ApiResp r;
    if (api_request("GET", "index.php?r=api/users&role=student&limit=10", NULL, &r) == 0 && r.json) {
        json_object *us = json_object_object_get(r.json, "users");
        int n = us ? (int)json_object_array_length(us) : 0;
        for (int i = 0; i < n; i++) {
            json_object *u = json_object_array_get_idx(us, i);
            mvprintw(9 + i, 2, "%-3d %-24s %6lld XP (Lv %lld)",
                     i + 1, js_str(u, "last_name", "?"),
                     (long long)json_object_get_int64(json_object_object_get(u, "xp")),
                     (long long)json_object_get_int64(json_object_object_get(u, "level")));
        }
    } else {
        ui_msg("Failed to load leaderboard.", 1);
    }
    api_resp_free(&r);
    ui_footer("Press any key to continue...");
    getch();
    return 0;
}

/* ------------------------------------------------------ AI TUTOR */

int screen_ai_chat(void)
{
    char q[400];
    ui_clear();
    ui_banner(stdscr);
    attron(COLOR_PAIR(1));
    mvwaddstr(stdscr, 5, 2, "AI TUTOR (local rule-based engine)");
    attroff(COLOR_PAIR(1));
    mvaddstr(7, 2, "Ask about recursion, loops, history, physics, grammar...");
    q[0] = 0;
    ui_prompt("Question:", q, sizeof q, 0);
    if (!q[0]) return 0;

    char req[600];
    snprintf(req, sizeof req, "{\"message\":\"%s\"}", q);
    ApiResp r;
    if (api_request("POST", "index.php?r=api/ai/chat", req, &r) == 0 && r.json &&
        json_object_get_boolean(json_object_object_get(r.json, "ok"))) {
        ui_clear();
        ui_banner(stdscr);
        attron(COLOR_PAIR(2));
        mvwaddstr(stdscr, 5, 2, "AI TUTOR REPLY");
        attroff(COLOR_PAIR(2));
        const char *reply = js_str(r.json, "reply", "");
        int y = 7;
        int col = 2;
        int cw = ui_cols();
        int rh = ui_rows();
        size_t n = strlen(reply);
        for (size_t i = 0; i < n; i++) {
            if (reply[i] == '\n' || col >= cw - 2) {
                y++; col = 2;
                if (y > rh - 4) break;
            }
            if (reply[i] == '\n') continue;
            mvwaddch(stdscr, y, col++, reply[i]);
        }
        ui_footer("Press any key to continue...");
        getch();
    } else {
        ui_msg(r.json ? api_err(&r) : "Network error", 1);
        sleep(2);
    }
    api_resp_free(&r);
    return 0;
}

/* --------------------------------------------------------- ADMIN */

int screen_admin(void)
{
    if (strcmp(g_me.role, "admin") != 0) {
        ui_msg("Admin access only.", 1);
        sleep(2);
        return 0;
    }
    static const char *items[] = {
        "1. Recent activity",
        "2. List users",
        "3. List reports",
        "4. List backups",
        "5. Create database backup",
        "6. Back to main menu",
    };
    for (;;) {
        int sel = ui_menu("ADMIN CONTROL PANEL", items, 6, 0);
        ApiResp r;
        if (sel == 0) {
            if (api_request("GET", "index.php?r=api/activity&limit=25", NULL, &r) == 0 && r.json) {
                json_object *a = json_object_object_get(r.json, "activity");
                print_rows("RECENT ACTIVITY (25)", a,
                           (const char *[]){ "created_at", "role", "first_name", "action" }, 4, 25);
            } else ui_msg("Failed.", 1);
        } else if (sel == 1) {
            if (api_request("GET", "index.php?r=api/users&limit=30", NULL, &r) == 0 && r.json) {
                json_object *us = json_object_object_get(r.json, "users");
                print_rows("USERS", us,
                           (const char *[]){ "id", "last_name", "role", "status" }, 4, 30);
            } else ui_msg("Failed.", 1);
        } else if (sel == 2) {
            if (api_request("GET", "index.php?r=api/reports", NULL, &r) == 0 && r.json) {
                json_object *re = json_object_object_get(r.json, "reports");
                print_rows("REPORTS", re,
                           (const char *[]){ "id", "title", "type", "format" }, 4, 25);
            } else ui_msg("Failed.", 1);
        } else if (sel == 3) {
            if (api_request("GET", "index.php?r=api/backups", NULL, &r) == 0 && r.json) {
                json_object *b = json_object_object_get(r.json, "backups");
                print_rows("BACKUPS", b,
                           (const char *[]){ "file", "size", "created" }, 3, 25);
            } else ui_msg("Failed.", 1);
        } else if (sel == 4) {
            if (api_request("POST", "index.php?r=api/backups", NULL, &r) == 0 && r.json &&
                json_object_get_boolean(json_object_object_get(r.json, "ok"))) {
                char msg[200];
                snprintf(msg, sizeof msg, "Backup created: %s", js_str(r.json, "file", "?"));
                ui_msg(msg, 0);
                sleep(2);
            } else ui_msg("Backup failed (is mysqldump installed?).", 1);
        } else break;
        api_resp_free(&r);
    }
    return 0;
}

/* ------------------------------------------------------- POLLING */

void screen_poll_notify(const char *msg, int error)
{
    ui_msg(msg, error);
    refresh();
}
