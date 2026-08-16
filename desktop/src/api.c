#include "api.h"

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <curl/curl.h>

char g_base_url[256] = "http://localhost:8080";
char g_token[512] = "";
ApiUser g_me;

struct buf {
    char *data;
    size_t len;
};

static size_t write_cb(void *ptr, size_t size, size_t nmemb, void *ud)
{
    struct buf *b = (struct buf *)ud;
    size_t n = size * nmemb;
    if (b->len + n + 1 > API_MAX_BODY)
        n = API_MAX_BODY - b->len - 1;
    memcpy(b->data + b->len, ptr, n);
    b->len += n;
    b->data[b->len] = '\0';
    return size * nmemb;
}

int api_init(void)
{
    curl_global_init(CURL_GLOBAL_ALL);
    return 0;
}

void api_set_base(const char *url)
{
    snprintf(g_base_url, sizeof g_base_url, "%s", url);
    size_t n = strlen(g_base_url);
    while (n > 0 && g_base_url[n - 1] == '/')
        g_base_url[--n] = '\0';
}

void api_set_token(const char *token)
{
    snprintf(g_token, sizeof g_token, "%s", token);
}

const char *js_str(json_object *o, const char *k, const char *def)
{
    if (!o) return def;
    json_object *v;
    if (json_object_object_get_ex(o, k, &v)) {
        const char *s = json_object_get_string(v);
        return s ? s : def;
    }
    return def;
}

int api_request(const char *method, const char *path,
                const char *body, ApiResp *out)
{
    CURL *curl;
    CURLcode res;
    struct buf b = { 0 };
    char url[512];
    struct curl_slist *headers = NULL;
    char auth[600];
    int ok = -1;

    memset(out, 0, sizeof *out);
    b.data = malloc(API_MAX_BODY);
    b.data[0] = '\0';

    snprintf(url, sizeof url, "%s/%s", g_base_url, path);
    curl = curl_easy_init();
    if (!curl) goto done;

    curl_easy_setopt(curl, CURLOPT_URL, url);
    curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, write_cb);
    curl_easy_setopt(curl, CURLOPT_WRITEDATA, &b);
    curl_easy_setopt(curl, CURLOPT_TIMEOUT, 15L);
    curl_easy_setopt(curl, CURLOPT_CONNECTTIMEOUT, 8L);

    if (g_token[0]) {
        snprintf(auth, sizeof auth, "Authorization: Bearer %s", g_token);
        headers = curl_slist_append(headers, auth);
    }
    if (body) {
        curl_easy_setopt(curl, CURLOPT_POSTFIELDS, body);
        headers = curl_slist_append(headers, "Content-Type: application/json");
    } else if (strcmp(method, "POST") == 0) {
        curl_easy_setopt(curl, CURLOPT_POSTFIELDS, "");
        headers = curl_slist_append(headers, "Content-Type: application/json");
    }
    if (headers)
        curl_easy_setopt(curl, CURLOPT_HTTPHEADER, headers);

    res = curl_easy_perform(curl);
    if (res != CURLE_OK) {
        out->code = 0;
        snprintf(b.data, API_MAX_BODY, "{\"ok\":false,\"error\":\"network:%s\"}",
                 curl_easy_strerror(res));
        goto done;
    }
    curl_easy_getinfo(curl, CURLINFO_RESPONSE_CODE, &out->code);
    out->body = strdup(b.data);
    out->json = json_tokener_parse(b.data);
    ok = 0;
done:
    if (curl) curl_easy_cleanup(curl);
    if (headers) curl_slist_free_all(headers);
    free(b.data);
    return ok;
}

void api_resp_free(ApiResp *r)
{
    if (r->body) { free(r->body); r->body = NULL; }
    if (r->json) { json_object_put(r->json); r->json = NULL; }
    r->code = 0;
}

const char *api_err(const ApiResp *r)
{
    if (r->json) {
        json_object *o = r->json, *e;
        if (json_object_object_get_ex(o, "error", &e))
            return json_object_get_string(e);
    }
    return "unknown error";
}
