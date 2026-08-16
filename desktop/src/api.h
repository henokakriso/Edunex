#ifndef EDUNEX_API_H
#define EDUNEX_API_H

#include <json-c/json.h>

#define API_MAX_BODY 65536

typedef struct {
    int code;              /* HTTP status code */
    char *body;            /* raw response body */
    json_object *json;     /* parsed JSON (may be NULL) */
} ApiResp;

typedef struct {
    int id;
    char first_name[80];
    char last_name[80];
    char email[150];
    char role[20];
    int xp;
    int level;
    int school_id;
    char student_id[40];
} ApiUser;

/* global server base URL and token */
extern char g_base_url[256];
extern char g_token[512];
extern ApiUser g_me;

int  api_init(void);
void api_set_base(const char *url);
void api_set_token(const char *token);

/* perform GET/POST JSON request; returns 0 on ok, -1 on error */
int  api_request(const char *method, const char *path,
                 const char *body, ApiResp *out);
void api_resp_free(ApiResp *r);
const char *api_err(const ApiResp *r); /* "ok":false -> error field or msg */

/* convenience: parse string field */
const char *js_str(json_object *o, const char *k, const char *def);

#endif
