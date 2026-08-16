#include "api.h"
#include "ui.h"
#include "screens.h"

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <pthread.h>
#include <time.h>
#include <curl/curl.h>

/* ---------- notification polling thread ---------- */
static int poll_running = 0;

static void *poll_worker(void *arg)
{
    (void)arg;
    ApiResp r;
    int last = -1;
    while (poll_running) {
        sleep(30);
        if (!g_token[0]) continue;
        if (api_request("GET", "index.php?r=api/notifications/poll", NULL, &r) == 0 && r.json) {
            int unread = (int)json_object_get_int64(
                json_object_object_get(r.json, "unread"));
            if (unread > 0 && last >= 0 && unread > last) {
                char msg[120];
                snprintf(msg, sizeof msg, "!! %d unread notification(s) !!",
                         unread);
                screen_poll_notify(msg, 0);
            }
            last = unread;
        }
        api_resp_free(&r);
    }
    return NULL;
}

static void poll_start(void)
{
    poll_running = 1;
    pthread_t th;
    pthread_create(&th, NULL, poll_worker, NULL);
    pthread_detach(th);
}

/* ---------------- main menu ---------------- */
static int main_menu(void)
{
    static const char *items[] = {
        "Dashboard",
        "Notifications",
        "Courses",
        "Attendance",
        "Calendar",
        "Leaderboard",
        "AI Tutor",
        "Messages",
        "Grades",
        "Admin panel",
        "Log out / switch user",
        "Quit",
    };
    static const char *teachers[] = {
        "Dashboard",
        "Notifications",
        "Courses",
        "Attendance",
        "Calendar",
        "Leaderboard",
        "AI Tutor",
        "Messages",
        "Grades",
        "Admin panel",
        "Log out / switch user",
        "Quit",
    };
    const char **menu = strcmp(g_me.role, "student") == 0 ? items : teachers;
    (void)items; (void)teachers;

    for (;;) {
        char title[300];
        snprintf(title, sizeof title,
                 "MAIN MENU  [%s %s - %s - %.40s]", g_me.first_name, g_me.last_name,
                 g_me.role, g_me.student_id[0] ? g_me.student_id : g_me.email);
        int sel = ui_menu(title, menu, 12, 0);
        switch (sel) {
        case 0:  screen_dashboard(); break;
        case 1:  screen_notifications(); break;
        case 2:  screen_courses(); break;
        case 3:  screen_attendance(); break;
        case 4:  screen_calendar(); break;
        case 5:  screen_leaderboard(); break;
        case 6:  screen_ai_chat(); break;
        case 7:  screen_messages(); break;
        case 8:  screen_grades(); break;
        case 9:  screen_admin(); break;
        case 10: return 1;   /* logout -> login screen */
        default: return 0;   /* quit */
        }
    }
}

int main(void)
{
    api_init();
    ui_init();
    atexit(ui_shutdown);
    poll_start();

    int running = 1;
    while (running) {
        g_token[0] = '\0';
        if (screen_login() != 0) break;   /* user gave up */
        ui_msg("Login OK. Connecting...", 0);
        usleep(300000);
        if (main_menu() == 0) break;      /* quit */
    }

    poll_running = 0;
    curl_global_cleanup();
    return 0;
}
