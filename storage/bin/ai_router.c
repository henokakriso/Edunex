/*
 * ai_router.c — Edunex adaptive AI router (libcurl + json-c)
 *
 * Sits between PHP and the Ollama C engine. Picks the best model per
 * request, keeps every model warm (zero-delay switching), streams
 * tokens back as NDJSON, and answers exact-repeat questions from a
 * local cache (instant response).
 *
 * Commands:
 *   ai_router chat                  reads request JSON from stdin:
 *       { "system": "...", "messages": [{role,content},...],
 *         "models": ["m1","m2",...], "tags": "math|code|general",
 *         "max_tokens": 180, "temperature": 0.5 }
 *   ai_router warm <model> [...]    preload all listed models (1-token
 *                                   warmup call, keep_alive 30m).
 *
 * Output (NDJSON to stdout, one line per event):
 *   R <model>       model chosen for this request
 *   C               cache hit (following T lines replayed instantly)
 *   T <text>        token delta (\\n escaped)
 *   D               done
 *   E <message>     error (PHP falls back)
 *
 * State: storage/cache/ai/router_state.json  (per-model EMA latency)
 * Cache: storage/cache/ai/<sha256>.cache     (model header + answer)
 * Exit codes: 0 ok, 1 engine error, 2 cancelled, 3 usage.
 */

#define _GNU_SOURCE
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdbool.h>
#include <stdarg.h>
#include <signal.h>
#include <unistd.h>
#include <sys/stat.h>
#include <time.h>
#include <errno.h>
#include <curl/curl.h>
#include <json-c/json.h>
#include <openssl/sha.h>

#define HOST_DEFAULT "http://127.0.0.1:11434"
#define KEEP_ALIVE "30m"
#define CACHE_TTL_S (24 * 3600)
#define STATE_FILE "router_state.json"

static volatile sig_atomic_t g_cancel = 0;
static void on_signal(int s) { g_cancel = 1; (void)s; }

static char g_cache_dir[4096];
static char g_host[256] = HOST_DEFAULT;

/* ---------- output helpers ---------- */
static void emit(const char *tag, const char *s) {
    printf("%s %s\n", tag, s);
    fflush(stdout);
}

/* Escape \n \r \\ so NDJSON stays single-line. */
static void emit_delta(const char *s) {
    fputs("T ", stdout);
    for (const char *p = s; *p; p++) {
        if (*p == '\n') fputs("\\n", stdout);
        else if (*p == '\r') fputs("\\r", stdout);
        else if (*p == '\\') fputs("\\\\", stdout);
        else putchar(*p);
    }
    putchar('\n');
    fflush(stdout);
}

static void emit_err(const char *fmt, ...) {
    char buf[1024];
    va_list ap; va_start(ap, fmt);
    vsnprintf(buf, sizeof buf, fmt, ap); va_end(ap);
    emit("E", buf);
}

/* ---------- streaming curl ---------- */
struct stream_ctx {
    char *linebuf; size_t line_len; size_t line_cap;
    char *full;    size_t full_len; size_t full_cap;
    long   start_ms; long latency_ms;
    bool   engine_done;
};

static long now_ms(void) {
    struct timespec ts; clock_gettime(CLOCK_MONOTONIC, &ts);
    return ts.tv_sec * 1000L + ts.tv_nsec / 1000000L;
}

/* Abort the transfer immediately when PHP kills us (Stop button / closed tab). */
static int xfer_cancel(void *clientp, curl_off_t dlt, curl_off_t dln,
                       curl_off_t ult, curl_off_t uln) {
    (void)clientp; (void)dlt; (void)dln; (void)ult; (void)uln;
    return g_cancel ? 1 : 0;
}

static void sb_append(char **buf, size_t *len, size_t *cap, const char *s, size_t n) {
    if (*len + n + 1 > *cap) {
        size_t ncap = (*cap ? *cap * 2 : 4096);
        while (ncap < *len + n + 1) ncap *= 2;
        char *nd = realloc(*buf, ncap);
        if (!nd) return;
        *buf = nd; *cap = ncap;
    }
    memcpy(*buf + *len, s, n);
    *len += n;
    (*buf)[*len] = 0;
}

static size_t stream_cb(void *ptr, size_t size, size_t nmemb, void *userp) {
    size_t n = size * nmemb;
    struct stream_ctx *c = (struct stream_ctx *)userp;
    const char *data = (const char *)ptr;
    size_t i = 0;
    while (i < n) {
        char *nl = memchr(data + i, '\n', n - i);
        size_t seg = nl ? (size_t)(nl - (data + i)) : (n - i);
        sb_append(&c->linebuf, &c->line_len, &c->line_cap, data + i, seg);
        if (nl) {
            struct json_object *obj = json_tokener_parse(c->linebuf);
            if (obj) {
                struct json_object *msg = NULL, *cont = NULL, *done = NULL;
                if (json_object_object_get_ex(obj, "message", &msg) &&
                    json_object_object_get_ex(msg, "content", &cont)) {
                    const char *delta = json_object_get_string(cont);
                    if (delta && *delta) {
                        emit_delta(delta);
                        sb_append(&c->full, &c->full_len, &c->full_cap, delta, strlen(delta));
                    }
                }
                if (json_object_object_get_ex(obj, "done", &done) &&
                    json_object_get_boolean(done)) {
                    c->engine_done = true;
                }
                json_object_put(obj);
            }
            c->line_len = 0;
            c->linebuf[0] = 0;
            i += seg + 1;
        } else {
            i += seg;
        }
    }
    return n;
}

/* Non-streaming POST /api/chat; returns malloc'd content or NULL. */
static char *ollama_chat(const char *model, json_object *messages,
                         double temp, int max_tokens) {
    json_object *body = json_object_new_object();
    json_object_object_add(body, "model", json_object_new_string(model));
    json_object_object_add(body, "messages", messages); /* takes ownership */
    json_object_object_add(body, "stream", json_object_new_boolean(false));
    json_object_object_add(body, "temperature", json_object_new_double(temp));
    json_object_object_add(body, "num_predict", json_object_new_int(max_tokens));
    json_object_object_add(body, "keep_alive", json_object_new_string(KEEP_ALIVE));
    json_object *opts = json_object_new_object();
    json_object_object_add(opts, "num_ctx", json_object_new_int(2048));
    json_object_object_add(opts, "num_threads", json_object_new_int(8));
    json_object_object_add(opts, "num_batch", json_object_new_int(512));
    json_object_object_add(body, "options", opts);

    const char *payload = json_object_to_json_string(body);
    char url[512];
    snprintf(url, sizeof url, "%s/api/chat", g_host);

    struct curl_slist *hdrs = NULL;
    hdrs = curl_slist_append(hdrs, "Content-Type: application/json");
    struct stream_ctx ctx = {0};

    CURL *ch = curl_easy_init();
    if (!ch) { json_object_put(body); return NULL; }
    curl_easy_setopt(ch, CURLOPT_URL, url);
    curl_easy_setopt(ch, CURLOPT_POST, 1L);
    curl_easy_setopt(ch, CURLOPT_POSTFIELDS, payload);
    curl_easy_setopt(ch, CURLOPT_HTTPHEADER, hdrs);
    curl_easy_setopt(ch, CURLOPT_TIMEOUT_MS, 170000L);
    curl_easy_setopt(ch, CURLOPT_NOSIGNAL, 1L);
    curl_easy_setopt(ch, CURLOPT_WRITEFUNCTION, stream_cb);
    curl_easy_setopt(ch, CURLOPT_WRITEDATA, &ctx);
    CURLcode rc = curl_easy_perform(ch);
    curl_slist_free_all(hdrs);
    curl_easy_cleanup(ch);
    json_object_put(body);
    if (rc != CURLE_OK) { free(ctx.linebuf); free(ctx.full); return NULL; }

    char *out = ctx.full ? strdup(ctx.full) : NULL;
    free(ctx.linebuf);
    free(ctx.full);
    return out;
}

/* ---------- cache ---------- */
static void sha256_hex(const char *input, char out[65]) {
    unsigned char h[32];
    SHA256((const unsigned char *)input, strlen(input), h);
    for (int i = 0; i < 32; i++) sprintf(out + i * 2, "%02x", h[i]);
}

static char *cache_path(const char *hex) {
    static char p[4096];
    snprintf(p, sizeof p, "%s/%s.cache", g_cache_dir, hex);
    return p;
}

static char *cache_lookup(const char *hex, char *model_out, size_t model_sz) {
    char *p = cache_path(hex);
    struct stat st;
    if (stat(p, &st) != 0) return NULL;
    if (time(NULL) - st.st_mtime > CACHE_TTL_S) return NULL;
    FILE *f = fopen(p, "r");
    if (!f) return NULL;
    char line[512];
    if (!fgets(line, sizeof line, f)) { fclose(f); return NULL; }
    if (strncmp(line, "model=", 6) != 0) { fclose(f); return NULL; }
    line[strcspn(line, "\n")] = 0;
    snprintf(model_out, model_sz, "%s", line + 6);
    long hdr_len = ftell(f);
    fseek(f, 0, SEEK_END);
    long sz = ftell(f) - hdr_len;
    fseek(f, hdr_len, SEEK_SET);
    char *content = malloc(sz + 1);
    size_t got = fread(content, 1, sz, f);
    content[got] = 0;
    fclose(f);
    return content;
}

static void cache_store(const char *hex, const char *model, const char *content) {
    char *p = cache_path(hex);
    char tmp[4096];
    snprintf(tmp, sizeof tmp, "%s.tmp", p);
    FILE *f = fopen(tmp, "w");
    if (!f) return;
    fprintf(f, "model=%s\n%s", model, content);
    fclose(f);
    rename(tmp, p);
}

/* ---------- latency state (EMA) ---------- */
static char *state_path(void) {
    static char p[4096];
    snprintf(p, sizeof p, "%s/%s", g_cache_dir, STATE_FILE);
    return p;
}

static double state_latency(json_object *state, const char *model) {
    struct json_object *e = NULL;
    if (state && json_object_object_get_ex(state, model, &e)) {
        struct json_object *l = NULL;
        if (json_object_object_get_ex(e, "lat_ms", &l))
            return json_object_get_double(l);
    }
    return -1;
}

static void state_update(json_object **state, const char *model, long lat_ms) {
    if (!state || !*state) {
        *state = json_object_new_object();
        if (!*state) return;
    }
    struct json_object *e = NULL;
    if (!json_object_object_get_ex(*state, model, &e)) {
        e = json_object_new_object();
        json_object_object_add(*state, model, e);
    }
    struct json_object *l = NULL, *n = NULL;
    double old = -1;
    int nval = 0;
    if (json_object_object_get_ex(e, "lat_ms", &l)) old = json_object_get_double(l);
    if (json_object_object_get_ex(e, "n", &n)) nval = json_object_get_int(n);
    double nw = old < 0 ? lat_ms : old * 0.7 + lat_ms * 0.3;
    json_object_object_del(e, "lat_ms");
    json_object_object_add(e, "lat_ms", json_object_new_double(nw));
    json_object_object_del(e, "n");
    json_object_object_add(e, "n", json_object_new_int(nval + 1));

    const char *out = json_object_to_json_string_ext(*state, JSON_C_TO_STRING_PRETTY);
    char tmp[4096];
    snprintf(tmp, sizeof tmp, "%s.tmp", state_path());
    FILE *f = fopen(tmp, "w");
    if (f) { fputs(out, f); fclose(f); rename(tmp, state_path()); }
}

static json_object *state_load(void) {
    FILE *f = fopen(state_path(), "r");
    if (!f) return NULL;
    fseek(f, 0, SEEK_END); long sz = ftell(f); fseek(f, 0, SEEK_SET);
    if (sz <= 0 || sz > 1 << 20) { fclose(f); return NULL; }
    char *buf = malloc(sz + 1);
    size_t got = fread(buf, 1, sz, f);
    buf[got] = 0;
    fclose(f);
    struct json_object *o = json_tokener_parse(buf);
    free(buf);
    return o;
}

/* ---------- topic detection ---------- */
static bool has_kw(const char *hay, const char **kws, int n) {
    for (int i = 0; i < n; i++)
        if (strcasestr(hay, kws[i])) return true;
    return false;
}

static bool is_identity_q(const char *msg) {
    static const char *kws[] = {"who are you", "what are you", "your name",
        "who made you", "who created you", "what is your name", "tell me about yourself"};
    return has_kw(msg, kws, sizeof kws / sizeof *kws);
}

static bool tags_math(const char *tags) {
    return tags && (strcasestr(tags, "math") || strcasestr(tags, "physics"));
}
static bool tags_code(const char *tags) {
    return tags && strcasestr(tags, "code");
}

/* ---------- model selection ---------- */
static const char *pick_model(json_object *req, json_object *state,
                              const char *tags, const char *models_csv) {
    static const char *math_kws[] = {"algebra","equation","calculus","geometry",
        "integral","derivative","solve","probability","physics","number","root",
        "math","arithmetic","fraction","percent","angle","triangle","graph"};
    static const char *code_kws[] = {"code","program","function","variable",
        "syntax","debug","array","loop","python","javascript","php","list","stack",
        "queue","node","pointer","string","algorithm","sort","recursion","binary",
        "tree","hash","compile","compile","class","object","api","database","sql"};

    char *msg = NULL;
    struct json_object *msgs = NULL;
    if (json_object_object_get_ex(req, "messages", &msgs) &&
        json_object_array_length(msgs) > 0) {
        struct json_object *last = json_object_array_get_idx(msgs, json_object_array_length(msgs) - 1);
        const char *s = json_object_get_string(last);
        msg = strdup(s ? s : "");
    }
    if (!msg) msg = strdup("");
    bool want_math = tags_math(tags) || has_kw(msg, math_kws, sizeof math_kws / sizeof *math_kws);
    bool want_code = tags_code(tags) || has_kw(msg, code_kws, sizeof code_kws / sizeof *code_kws);
    bool want_id = is_identity_q(msg);
    free(msg);

    /* candidate list, preference order */
    enum { MAXC = 16 };
    const char *cand[MAXC];
    int nc = 0;
    const char *m = models_csv;
    char copy[2048];
    snprintf(copy, sizeof copy, "%s", m ? m : "");
    for (char *tok = strtok(copy, ","); tok && nc < MAXC; tok = strtok(NULL, ",")) {
        while (*tok == ' ' || *tok == '[' || *tok == '"' || *tok == '\'') tok++;
        size_t tl = strlen(tok);
        while (tl && (tok[tl-1] == ' ' || tok[tl-1] == ']' || tok[tl-1] == '"' || tok[tl-1] == '\'')) tok[--tl] = 0;
        if (*tok) cand[nc++] = strdup(tok);
    }
    if (nc == 0) { cand[0] = "edunex-tutor"; nc = 1; }

    /* identity questions -> biggest instruct model (follows identity best) */
    if (want_id) {
        for (int i = 0; i < nc; i++)
            if (strstr(cand[i], "3b")) return cand[i];
    }

    /* math -> deepseek-r1 (reasoning), then phi3 */
    if (want_math) {
        for (int i = 0; i < nc; i++)
            if (strstr(cand[i], "deepseek-r1")) return cand[i];
        for (int i = 0; i < nc; i++)
            if (strstr(cand[i], "phi3")) return cand[i];
    }
    /* code -> phi3 (compact + technical), then deepseek-r1 */
    if (want_code) {
        for (int i = 0; i < nc; i++)
            if (strstr(cand[i], "phi3")) return cand[i];
        for (int i = 0; i < nc; i++)
            if (strstr(cand[i], "deepseek-r1")) return cand[i];
    }
    /* general chat: accuracy first — biggest instruct model, smallest as emergency */
    const char *tier[] = {"3b", "2b", "1b", "0.5b"};
    for (size_t t = 0; t < sizeof tier / sizeof *tier; t++)
        for (int i = 0; i < nc; i++)
            if (strstr(cand[i], tier[t])) return cand[i];
    /* adaptive: pick lowest EMA latency among the rest */
    double best = -1; const char *bm = cand[0];
    for (int i = 0; i < nc; i++) {
        double l = state_latency(state, cand[i]);
        if (l < 0) { bm = cand[i]; break; }
        if (best < 0 || l < best) { best = l; bm = cand[i]; }
    }
    return bm;
}

/* ---------- chat mode ---------- */
static int cmd_chat(void) {
    char *reqbuf = malloc(1 << 20);
    if (!reqbuf) return 3;
    size_t req_len = fread(reqbuf, 1, (1 << 20) - 1, stdin);
    reqbuf[req_len] = 0;

    struct json_object *req = json_tokener_parse(reqbuf);
    free(reqbuf);
    if (!req) { emit_err("bad request json"); return 3; }

    struct json_object *j_messages = NULL;
    const char *tags = NULL, *models_csv = NULL, *system = NULL;
    struct json_object *o;
    if (json_object_object_get_ex(req, "messages", &j_messages) &&
        !json_object_is_type(j_messages, json_type_array)) j_messages = NULL;
    if (json_object_object_get_ex(req, "tags", &o)) tags = json_object_get_string(o);
    if (json_object_object_get_ex(req, "models", &o)) models_csv = json_object_get_string(o);
    if (json_object_object_get_ex(req, "system", &o)) system = json_object_get_string(o);

    int max_tokens = 180;
    double temperature = 0.5;
    if (json_object_object_get_ex(req, "max_tokens", &o)) max_tokens = json_object_get_int(o);
    if (json_object_object_get_ex(req, "temperature", &o)) temperature = json_object_get_double(o);

    if (!j_messages) { emit_err("messages required"); json_object_put(req); return 3; }

    /* cache key = system + roles/contents */
    struct json_object *key = json_object_new_array();
    if (system) json_object_array_add(key, json_object_new_string(system));
    for (size_t i = 0; i < json_object_array_length(j_messages); i++) {
        struct json_object *m = json_object_array_get_idx(j_messages, i);
        struct json_object *c = NULL;
        if (json_object_object_get_ex(m, "content", &c))
            json_object_array_add(key, json_object_new_string(json_object_get_string(c)));
    }
    const char *keystr = json_object_to_json_string(key);
    char hex[65];
    sha256_hex(keystr, hex);
    json_object_put(key);

    char cached_model[256] = "";
    char *cached = cache_lookup(hex, cached_model, sizeof cached_model);
    if (cached && *cached) {
        emit("C", cached_model);
        emit_delta(cached);
        emit("D", "");
        free(cached);
        json_object_put(req);
        return 0;
    }
    free(cached);

    json_object *state = state_load();
    const char *model = pick_model(req, state, tags, models_csv);
    emit("R", model);

    /* streaming request */
    json_object *body = json_object_new_object();
    json_object_object_add(body, "model", json_object_new_string(model));
    json_object_get(j_messages);
    json_object_object_add(body, "messages", j_messages);
    json_object_object_add(body, "stream", json_object_new_boolean(true));
    json_object_object_add(body, "temperature", json_object_new_double(temperature));
    json_object_object_add(body, "num_predict", json_object_new_int(max_tokens));
    json_object_object_add(body, "keep_alive", json_object_new_string(KEEP_ALIVE));
    json_object *opts = json_object_new_object();
    json_object_object_add(opts, "num_ctx", json_object_new_int(2048));
    json_object_object_add(opts, "num_threads", json_object_new_int(8));
    json_object_object_add(opts, "num_batch", json_object_new_int(512));
    json_object_object_add(body, "options", opts);

    const char *payload = json_object_to_json_string(body);
    char url[512];
    snprintf(url, sizeof url, "%s/api/chat", g_host);

    struct stream_ctx ctx = {0};
    ctx.start_ms = now_ms();
    size_t n_msgs = json_object_array_length(j_messages);

    struct curl_slist *hdrs = NULL;
    hdrs = curl_slist_append(hdrs, "Content-Type: application/json");
    CURL *ch = curl_easy_init();
    CURLcode rc = CURLE_FAILED_INIT;
    if (ch) {
        curl_easy_setopt(ch, CURLOPT_URL, url);
        curl_easy_setopt(ch, CURLOPT_POST, 1L);
        curl_easy_setopt(ch, CURLOPT_POSTFIELDS, payload);
        curl_easy_setopt(ch, CURLOPT_HTTPHEADER, hdrs);
        curl_easy_setopt(ch, CURLOPT_TIMEOUT_MS, 170000L);
        curl_easy_setopt(ch, CURLOPT_NOSIGNAL, 1L);
        curl_easy_setopt(ch, CURLOPT_NOPROGRESS, 0L);
        curl_easy_setopt(ch, CURLOPT_XFERINFOFUNCTION, xfer_cancel);
        curl_easy_setopt(ch, CURLOPT_WRITEFUNCTION, stream_cb);
        curl_easy_setopt(ch, CURLOPT_WRITEDATA, &ctx);
        rc = curl_easy_perform(ch);
        curl_easy_cleanup(ch);
    }
    curl_slist_free_all(hdrs);
    json_object_put(body);
    json_object_put(req);

    if (g_cancel) { free(ctx.linebuf); free(ctx.full); return 2; }
    if (rc != CURLE_OK || !ctx.engine_done || !ctx.full || !*ctx.full) {
        emit_err("engine: %s", curl_easy_strerror(rc));
        free(ctx.linebuf); free(ctx.full);
        return 1;
    }

    ctx.latency_ms = now_ms() - ctx.start_ms;
    state_update(&state, model, ctx.latency_ms);
    if (state) json_object_put(state);

    /* cache only short conversations (<=2 turns) so repeats stay instant */
    if (n_msgs <= 3 && ctx.full_len < 2000)
        cache_store(hex, model, ctx.full);

    emit("D", "");
    free(ctx.linebuf);
    free(ctx.full);
    return 0;
}

/* ---------- warm mode ---------- */
static int cmd_warm(int argc, char **argv) {
    int i = 2;
    for (; i < argc; i++) {
        struct json_object *sys = json_object_new_array();
        struct json_object *m = json_object_new_object();
        json_object_object_add(m, "role", json_object_new_string("user"));
        json_object_object_add(m, "content", json_object_new_string("hi"));
        json_object_array_add(sys, m);
        json_object *r = json_object_new_object();
        json_object_object_add(r, "model", json_object_new_string(argv[i]));
        json_object_object_add(r, "messages", sys);
        json_object_object_add(r, "stream", json_object_new_boolean(false));
        json_object_object_add(r, "keep_alive", json_object_new_string(KEEP_ALIVE));
        json_object_object_add(r, "num_predict", json_object_new_int(1));
        json_object_object_add(r, "options", json_object_new_object());
        const char *payload = json_object_to_json_string(r);
        char url[512];
        snprintf(url, sizeof url, "%s/api/chat", g_host);
        struct curl_slist *hdrs = NULL;
        hdrs = curl_slist_append(hdrs, "Content-Type: application/json");
        struct stream_ctx ctx = {0};
        CURL *ch = curl_easy_init();
        CURLcode rc = CURLE_FAILED_INIT;
        if (ch) {
            curl_easy_setopt(ch, CURLOPT_URL, url);
            curl_easy_setopt(ch, CURLOPT_POST, 1L);
            curl_easy_setopt(ch, CURLOPT_POSTFIELDS, payload);
            curl_easy_setopt(ch, CURLOPT_HTTPHEADER, hdrs);
            curl_easy_setopt(ch, CURLOPT_TIMEOUT_MS, 60000L);
            curl_easy_setopt(ch, CURLOPT_NOSIGNAL, 1L);
            curl_easy_setopt(ch, CURLOPT_WRITEFUNCTION, stream_cb);
            curl_easy_setopt(ch, CURLOPT_WRITEDATA, &ctx);
            rc = curl_easy_perform(ch);
            curl_easy_cleanup(ch);
        }
        curl_slist_free_all(hdrs);
        json_object_put(r);
        free(ctx.linebuf); free(ctx.full);
        if (rc == CURLE_OK) { emit("W", argv[i]); }
        else { emit_err("warm %s: %s", argv[i], curl_easy_strerror(rc)); }
    }
    return 0;
}

int main(int argc, char **argv) {
    signal(SIGINT, on_signal);
    signal(SIGTERM, on_signal);

    const char *cache = getenv("EDUNEX_CACHE");
    if (cache) snprintf(g_cache_dir, sizeof g_cache_dir, "%s", cache);
    else snprintf(g_cache_dir, sizeof g_cache_dir, "%s", "storage/cache/ai");
    mkdir(g_cache_dir, 0755);

    const char *host = getenv("EDUNEX_AI_HOST");
    if (host) snprintf(g_host, sizeof g_host, "%s", host);

    curl_global_init(CURL_GLOBAL_DEFAULT);
    int rc;
    if (argc >= 2 && strcmp(argv[1], "chat") == 0) {
        rc = cmd_chat();
    } else if (argc >= 2 && strcmp(argv[1], "warm") == 0) {
        rc = cmd_warm(argc, argv);
    } else {
        fprintf(stderr, "usage: ai_router chat | warm <model...>\n");
        rc = 3;
    }
    curl_global_cleanup();
    return rc;
}
