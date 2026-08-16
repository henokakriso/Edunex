/*
 * ai_fast.c — Edunex native C AI engine (libcurl + json-c)
 *
 * Talks directly to a local llama.cpp/Ollama endpoint with tuned parameters
 * and streams heavy generation jobs with progress + cancellation support.
 *
 * Modes:
 *   ai_fast course <textfile> <nquestions> <title> <outdir> [host] [model]
 *       Phase 1: short summary   -> outdir/summary.txt
 *       Phase 2: n MCQs generated in ONE streaming call, items written to
 *                outdir/questions.ndjson as soon as they complete.
 *
 * Progress protocol (appended to outdir/progress.log):
 *   P stage=summary
 *   P stage=questions cur=3 total=10
 *   D ok | D cancelled
 *   E <message>
 *
 * Cancel: presence of outdir/cancel file, or SIGTERM/SIGINT.
 * Exit codes: 0 ok, 1 engine error, 2 cancelled, 3 usage.
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdbool.h>
#include <stdarg.h>
#include <signal.h>
#include <unistd.h>
#include <sys/stat.h>
#include <curl/curl.h>
#include <json-c/json.h>

static volatile sig_atomic_t g_cancel = 0;
static FILE *g_prog = NULL;

static void on_signal(int s) { g_cancel = 1; (void)s; }

static bool cancel_requested(const char *dir) {
    if (g_cancel) return true;
    static char path[4096];
    snprintf(path, sizeof path, "%s/cancel", dir);
    return access(path, F_OK) == 0;
}

static void prog(const char *fmt, ...) {
    if (!g_prog) return;
    va_list ap; va_start(ap, fmt);
    vfprintf(g_prog, fmt, ap); va_end(ap);
    fflush(g_prog);
}

/* --- libcurl helpers --- */
struct wbuf { char *data; size_t len; size_t cap; };

static size_t write_cb(void *ptr, size_t size, size_t nmemb, void *userp) {
    size_t n = size * nmemb;
    struct wbuf *b = (struct wbuf *)userp;
    if (b->len + n + 1 > b->cap) {
        size_t ncap = (b->len + n + 1) * 2;
        char *nd = realloc(b->data, ncap);
        if (!nd) return 0;
        b->data = nd; b->cap = ncap;
    }
    memcpy(b->data + b->len, ptr, n);
    b->len += n;
    b->data[b->len] = 0;
    return n;
}

static int xfer_cb(void *clientp, curl_off_t dlt, curl_off_t dln,
                   curl_off_t ult, curl_off_t uln) {
    (void)dlt; (void)dln; (void)ult; (void)uln;
    return cancel_requested((const char *)clientp) ? 1 : 0;
}

static char *g_host = "http://127.0.0.1:11434";
static char *g_model = "edunex-tutor";

/* POST /api/chat non-streaming; returns message content (caller frees) or NULL. */
static char *ollama_chat(json_object *messages, double temp, int max_tokens,
                         bool force_json, const char *dir, long timeout_ms) {
    json_object *body = json_object_new_object();
    json_object_object_add(body, "model", json_object_new_string(g_model));
    json_object_get(messages); /* object_add takes ownership of this ref */
    json_object_object_add(body, "messages", messages);
    json_object_object_add(body, "stream", json_object_new_boolean(false));
    json_object_object_add(body, "temperature", json_object_new_double(temp));
    json_object_object_add(body, "num_predict", json_object_new_int(max_tokens));
    if (force_json)
        json_object_object_add(body, "format", json_object_new_string("json"));
    json_object *opts = json_object_new_object();
    json_object_object_add(opts, "temperature", json_object_new_double(temp));
    json_object_object_add(opts, "num_predict", json_object_new_int(max_tokens));
    json_object_object_add(opts, "num_ctx", json_object_new_int(1024));
    json_object_object_add(opts, "num_threads", json_object_new_int(6));
    json_object_object_add(opts, "num_batch", json_object_new_int(512));
    json_object_object_add(opts, "keep_alive", json_object_new_string("30m"));
    json_object_object_add(body, "options", opts);
    const char *payload = json_object_to_json_string(body);

    char url[1024];
    snprintf(url, sizeof url, "%s/api/chat", g_host);
    CURL *ch = curl_easy_init();
    if (!ch) { json_object_put(body); return NULL; }
    struct wbuf wb = {0};
    struct curl_slist *hdrs = curl_slist_append(NULL, "Content-Type: application/json");
    curl_easy_setopt(ch, CURLOPT_URL, url);
    curl_easy_setopt(ch, CURLOPT_POST, 1L);
    curl_easy_setopt(ch, CURLOPT_POSTFIELDS, payload);
    curl_easy_setopt(ch, CURLOPT_HTTPHEADER, hdrs);
    curl_easy_setopt(ch, CURLOPT_WRITEFUNCTION, write_cb);
    curl_easy_setopt(ch, CURLOPT_WRITEDATA, &wb);
    curl_easy_setopt(ch, CURLOPT_XFERINFOFUNCTION, xfer_cb);
    curl_easy_setopt(ch, CURLOPT_XFERINFODATA, (void *)dir);
    curl_easy_setopt(ch, CURLOPT_NOPROGRESS, 0L);
    curl_easy_setopt(ch, CURLOPT_NOSIGNAL, 1L);
    curl_easy_setopt(ch, CURLOPT_CONNECTTIMEOUT_MS, 5000L);
    curl_easy_setopt(ch, CURLOPT_TIMEOUT_MS, timeout_ms);
    curl_easy_setopt(ch, CURLOPT_TCP_KEEPALIVE, 1L);
    CURLcode rc = curl_easy_perform(ch);
    long http = 0;
    curl_easy_getinfo(ch, CURLINFO_RESPONSE_CODE, &http);
    curl_slist_free_all(hdrs);
    curl_easy_cleanup(ch);
    json_object_put(body);
    if (rc != CURLE_OK || http != 200 || wb.len == 0) {
        free(wb.data);
        return NULL;
    }
    json_object *resp = json_tokener_parse(wb.data);
    free(wb.data);
    if (!resp) return NULL;
    char *out = NULL;
    json_object *msg = NULL;
    if (json_object_object_get_ex(resp, "message", &msg)) {
        json_object *content = NULL;
        if (json_object_object_get_ex(msg, "content", &content)) {
            const char *s = json_object_get_string(content);
            if (s) out = strdup(s);
        }
    }
    json_object_put(resp);
    return out;
}

/* Strip a leading option label like "a." / "b)" / "c " / "d-" */
static const char *strip_label(const char *s) {
    if (!s) return NULL;
    while (*s == ' ' || *s == '\t') s++;
    if (s[0] && ((s[0] >= 'a' && s[0] <= 'z') || (s[0] >= 'A' && s[0] <= 'Z'))) {
        if (s[1] == '.' || s[1] == ')' || s[1] == ' ' || s[1] == '-' || s[1] == ':')
            s += 2;
    }
    while (*s == ' ' || *s == '\t') s++;
    return s;
}

/* --- sequential question generation ---
 * One question per Ollama call (short prompt, small text window):
 * reliable JSON via format=json, live progress, cancellable between calls.
 * Returns: 0 ok, 1 engine error, 2 cancelled.
 */
static int gen_questions_sequential(const char *text, int n, const char *title,
                                    const char *dir, FILE *out, int nch, char **chunks) {
    if (nch <= 0) { nch = 1; }
    for (int i = 0; i < n; i++) {
        if (cancel_requested(dir)) return 2;
        const char *window = chunks ? chunks[i % nch] : text;
        char prompt[9000];
        snprintf(prompt, sizeof prompt,
                 "Write ONE multiple choice quiz question about the topic. "
                 "The options must be real answer texts, not letters. "
                 "JSON: {\"question\":\"\",\"options\":[\"first answer\",\"second answer\",\"third answer\",\"fourth answer\"],\"answer\":\"\",\"explanation\":\"\"}. "
                 "The answer field must exactly equal one of the four options. "
                 "Topic: %s\nText:\n%s", title, window);

        json_object *sys = json_object_new_object();
        json_object_object_add(sys, "role", json_object_new_string("system"));
        json_object_object_add(sys, "content", json_object_new_string("You are a teacher. Reply with one JSON object only."));
        json_object *usr = json_object_new_object();
        json_object_object_add(usr, "role", json_object_new_string("user"));
        json_object_object_add(usr, "content", json_object_new_string(prompt));
        json_object *arr = json_object_new_array();
        json_object_array_add(arr, sys);
        json_object_array_add(arr, usr);

        char *raw = ollama_chat(arr, 0.4, 200, true, dir, 90000);
        json_object_put(arr);
        if (raw) {
            /* strip anything outside the outermost braces */
            char *f = strchr(raw, '{');
            char *l = strrchr(raw, '}');
            if (f && l && l > f) *(l + 1) = 0;
            json_object *it = f ? json_tokener_parse(f) : NULL;
            if (it && json_object_is_type(it, json_type_object)) {
                json_object *q = NULL, *opts = NULL, *ans = NULL, *exp = NULL;
                json_object_object_get_ex(it, "question", &q);
                json_object_object_get_ex(it, "options", &opts);
                json_object_object_get_ex(it, "answer", &ans);
                json_object_object_get_ex(it, "explanation", &exp);
                const char *qq = q ? json_object_get_string(q) : NULL;
                if (qq && json_object_is_type(opts, json_type_array)) {
                    int nopt = json_object_array_length(opts);
                    if (nopt >= 2) {
                        const char *av = ans ? json_object_get_string(ans) : NULL;
                        int answer_idx = -1;
                        for (int k = 0; k < nopt && k < 8; k++) {
                            const char *o = json_object_get_string(json_object_array_get_idx(opts, k));
                            const char *os = o ? strip_label(o) : NULL;
                            const char *as = strip_label(av);
                            if (os && as && strcmp(os, as) == 0) { answer_idx = k; break; }
                        }
                        if (answer_idx < 0 && av) {
                            const char *t = av;
                            while (*t == ' ') t++;
                            if (t[0] >= 'a' && t[0] <= 'z') {
                                int li = t[0] - 'a';
                                if (li < nopt) answer_idx = li;
                            }
                        }
                        /* rewrite options without labels */
                        json_object *newopts = json_object_new_array();
                        for (int k = 0; k < nopt && k < 8; k++) {
                            const char *o = json_object_get_string(json_object_array_get_idx(opts, k));
                            json_object_array_add(newopts, json_object_new_string(o ? strip_label(o) : ""));
                        }
                        json_object_object_del(it, "options");
                        json_object_object_add(it, "options", newopts);
                        const char *answer_text = NULL;
                        if (answer_idx >= 0)
                            answer_text = strip_label(json_object_get_string(json_object_array_get_idx(newopts, answer_idx)));
                        if (answer_text) {
                            json_object_object_del(it, "answer");
                            json_object_object_add(it, "answer", json_object_new_string(answer_text));
                            const char *line = json_object_to_json_string(it);
                            fputs(line, out);
                            fputc('\n', out);
                            fflush(out);
                            prog("P stage=questions cur=%d total=%d\n", i + 1, n);
                        }
                    }
                }
                json_object_put(it);
            }
            free(raw);
        }
    }
    return 0;
}

/* Split text into n chunks at paragraph boundaries. Returns malloc'd array. */
static char **split_chunks(const char *text, int n, int *out_n) {
    char *copy = strdup(text);
    int cap = 4096, cnt = 0;
    char **chunks = malloc(sizeof(char *) * cap);
    char *save = NULL;
    for (char *tok = strtok_r(copy, "\n", &save); tok; tok = strtok_r(NULL, "\n", &save)) {
        char *line = tok;
        while (*line == ' ' || *line == '\t' || *line == '\r') line++;
        if (*line == '\0') continue;
        if (cnt >= cap) { cap *= 2; chunks = realloc(chunks, sizeof(char *) * cap); }
        chunks[cnt++] = strdup(line);
    }
    free(copy);
    if (cnt == 0) { *out_n = 0; free(chunks); return NULL; }
    *out_n = cnt;
    if (n <= 1 || cnt <= n) return chunks;
    /* merge lines into n groups */
    int per = (cnt + n - 1) / n;
    int groups = 0;
    char **out = malloc(sizeof(char *) * n);
    for (int g = 0; g < n; g++) {
        int start = g * per;
        int end = (g + 1) * per;
        if (end > cnt) end = cnt;
        if (start >= cnt) break;
        size_t len = 1;
        for (int k = start; k < end; k++) len += strlen(chunks[k]) + 1;
        char *buf = malloc(len);
        buf[0] = 0;
        for (int k = start; k < end; k++) { strcat(buf, chunks[k]); strcat(buf, "\n"); }
        out[g] = buf;
        groups++;
    }
    for (int i = 0; i < cnt; i++) free(chunks[i]);
    free(chunks);
    *out_n = groups;
    return out;
}

static char *read_file(const char *path) {
    FILE *f = fopen(path, "rb");
    if (!f) return NULL;
    fseek(f, 0, SEEK_END);
    long sz = ftell(f);
    fseek(f, 0, SEEK_SET);
    if (sz <= 0 || sz > 60000) { fclose(f); return NULL; }
    char *buf = malloc(sz + 1);
    if (fread(buf, 1, sz, f) != (size_t)sz) { free(buf); fclose(f); return NULL; }
    buf[sz] = 0;
    fclose(f);
    return buf;
}

static int mode_course(int argc, char **argv) {
    if (argc < 6) {
        fprintf(stderr, "usage: ai_fast course <textfile> <n> <title> <outdir> [host] [model]\n");
        return 3;
    }
    const char *textfile = argv[2];
    int n = atoi(argv[3]);
    const char *title = argv[4];
    const char *dir = argv[5];
    if (argc >= 7) g_host = argv[6];
    if (argc >= 8) g_model = argv[7];
    if (n < 1) n = 5;

    mkdir(dir, 0755);
    char prog_path[4096];
    snprintf(prog_path, sizeof prog_path, "%s/progress.log", dir);
    g_prog = fopen(prog_path, "a");
    prog("P stage=summary cur=0 total=1\n");

    char *text = read_file(textfile);
    if (!text) { prog("E cannot-read-text\n"); if (g_prog) fclose(g_prog); return 1; }

    /* Phase 1: summary */
    char sumsys[512], sumusr[512];
    snprintf(sumsys, sizeof sumsys,
             "Summarize the following textbook text in at most 50 words for a course description. Plain text only, no markdown.");
    snprintf(sumusr, sizeof sumusr, "Course: %s\n\n%s", title, text);
    json_object *smsgs = json_object_new_array();
    json_object *s1 = json_object_new_object();
    json_object_object_add(s1, "role", json_object_new_string("system"));
    json_object_object_add(s1, "content", json_object_new_string(sumsys));
    json_object *s2 = json_object_new_object();
    json_object_object_add(s2, "role", json_object_new_string("user"));
    json_object_object_add(s2, "content", json_object_new_string(sumusr));
    json_object_array_add(smsgs, s1);
    json_object_array_add(smsgs, s2);
    char *summary = ollama_chat(smsgs, 0.3, 80, false, dir, 90000);
    json_object_put(smsgs);
    if (summary) {
        char spath[4096];
        snprintf(spath, sizeof spath, "%s/summary.txt", dir);
        FILE *sf = fopen(spath, "w");
        if (sf) { fputs(summary, sf); fclose(sf); }
        free(summary);
    } else if (!cancel_requested(dir)) {
        /* engine down — abort so PHP can fall back */
        prog("E ollama-down\n");
        free(text);
        if (g_prog) fclose(g_prog);
        return 1;
    }

    if (cancel_requested(dir)) { prog("D cancelled\n"); free(text); if (g_prog) fclose(g_prog); return 2; }

    /* Phase 2: questions (one Ollama call per question — reliable JSON + live progress) */
    prog("P stage=questions cur=0 total=%d\n", n);
    char qpath[4096];
    snprintf(qpath, sizeof qpath, "%s/questions.ndjson", dir);
    FILE *qf = fopen(qpath, "w");
    int rc = 0;
    if (qf) {
        if (n > 0) {
            int nch = 0;
            char **chunks = split_chunks(text, n, &nch);
            rc = gen_questions_sequential(text, n, title, dir, qf, nch, chunks);
            for (int i = 0; i < nch; i++) free(chunks[i]);
            free(chunks);
        }
        fclose(qf);
    } else {
        prog("E cannot-write-output\n");
    }
    free(text);

    if (rc == 2 || cancel_requested(dir)) { prog("D cancelled\n"); if (g_prog) fclose(g_prog); return 2; }
    prog("D ok\n");
    if (g_prog) fclose(g_prog);
    return 0;
}

int main(int argc, char **argv) {
    signal(SIGTERM, on_signal);
    signal(SIGINT, on_signal);
    curl_global_init(CURL_GLOBAL_DEFAULT);
    int rc = 3;
    if (argc >= 2 && strcmp(argv[1], "course") == 0) rc = mode_course(argc, argv);
    else fprintf(stderr, "usage: ai_fast course ...\n");
    curl_global_cleanup();
    return rc;
}
