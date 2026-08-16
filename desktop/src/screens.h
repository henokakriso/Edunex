#ifndef EDUNEX_SCREENS_H
#define EDUNEX_SCREENS_H

int screen_login(void);
int screen_dashboard(void);
int screen_notifications(void);
int screen_courses(void);
int screen_grades(void);
int screen_attendance(void);
int screen_messages(void);
int screen_calendar(void);
int screen_leaderboard(void);
int screen_ai_chat(void);
int screen_admin(void);
void screen_poll_notify(const char *msg, int error);

#endif
