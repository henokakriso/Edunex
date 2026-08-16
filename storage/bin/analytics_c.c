/*
 * analytics_c.c — Edunex analytics engine (native C helper)
 *
 * Reads a CSV report from stdin, computes the derived analytics the
 * PHP pages need, and prints the results as JSON to stdout:
 *
 *   #series                       (date,value lines — time series)
 *   2026-07-06,1
 *   ...
 *   #items                        (label,value lines — e.g. students per course)
 *   Mathematics 101,2
 *   ...
 *   #scores                       (plain numeric lines — e.g. avg progress)
 *   67
 *   0
 *
 * Output (single JSON object on stdout):
 *   {
 *     "series": { "total": 5, "mean": 0.2, "max": 2,
 *                 "growth": 40.0, "ma": [0.0, 0.3, ...] },
 *     "items":  [ { "label": "Mathematics 101", "value": 2,
 *                   "pct": 66.7, "rank": 1 }, ... ],
 *     "scores": { "mean": 33.5, "min": 0.0, "max": 67.0, "median": 33.5 }
 *   }
 *
 * Anything unparseable is skipped; empty sections produce empty output.
 * Compile: gcc -O2 -o storage/bin/analytics_c storage/bin/analytics_c.c
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#define MAX_ROWS  512
#define MAX_LEN   160

typedef struct {
    char label[MAX_LEN];
    double value;
    int    rank;
    double pct;
} Item;

typedef struct {
    char label[MAX_LEN];
    double value;
    double ma;
} SeriesPoint;

static double xs[MAX_ROWS];      /* raw series values */
static int     nxs = 0;
static SeriesPoint ma_pts[MAX_ROWS];
static int     nma = 0;

static Item    items[MAX_ROWS];
static int     nitems = 0;

static double  scores[MAX_ROWS];
static int     nscores = 0;

static char    out[1 << 16];
static size_t  olen = 0;

static void emit(const char *s);
static void emit_label_json(const char *s);
static void emit(const char *s)
{
    size_t l = strlen(s);
    if (olen + l + 1 < sizeof(out)) {
        memcpy(out + olen, s, l);
        olen += l;
    }
}

static void fmt_double(char *buf, size_t n, double v)
{
    if (v == (double)(long long)v)
        snprintf(buf, n, "%lld", (long long)v);
    else
        snprintf(buf, n, "%.1f", v);
}

static void trim(char *s)
{
    char *p = s, *q;
    while (*p == ' ' || *p == '\t') p++;
    if (p != s) memmove(s, p, strlen(p) + 1);
    q = s + strlen(s) - 1;
    while (q >= s && (*q == ' ' || *q == '\t' || *q == '\r')) *q-- = '\0';
}

static void analyze_series(void)
{
    double total = 0, max = 0, first = 0, second = 0;
    int i, half;

    if (nxs == 0) return;
    half = nxs / 2;
    for (i = 0; i < nxs; i++) {
        total += xs[i];
        if (i < half) first += xs[i]; else second += xs[i];
        if (i == 0 || xs[i] > max) max = xs[i];
    }

    for (i = 0; i < nma; i++) {
        double acc = 0;
        int k, cnt = 0;
        for (k = i - 1; k <= i + 1; k++)
            if (k >= 0 && k < nxs) { acc += xs[k]; cnt++; }
        ma_pts[i].ma = cnt ? acc / cnt : 0;
    }

    char t1[32], t2[32], t3[32], t4[32];
    fmt_double(t1, sizeof t1, total);
    fmt_double(t2, sizeof t2, nxs ? total / nxs : 0);
    fmt_double(t3, sizeof t3, max);
    fmt_double(t4, sizeof t4, first > 0 ? (second - first) / first * 100.0 : 0.0);

    emit("\"series\":{\"total\":");
    emit(t1);
    emit(",\"mean\":");
    emit(t2);
    emit(",\"max\":");
    emit(t3);
    emit(",\"growth\":");
    emit(t4);
    emit(",\"ma\":[");
    for (i = 0; i < nma; i++) {
        char b[32];
        fmt_double(b, sizeof b, ma_pts[i].ma);
        emit(b);
        if (i + 1 < nma) emit(",");
    }
    emit("]}");
}

static int cmp_items(const void *a, const void *b)
{
    double va = ((const Item *)b)->value - ((const Item *)a)->value;
    return va > 0 ? 1 : va < 0 ? -1 : 0;
}

static void analyze_items(void)
{
    int i, j;
    double total = 0;

    if (nitems == 0) return;
    for (i = 0; i < nitems; i++) total += items[i].value;

    for (i = 0; i < nitems; i++)
        items[i].pct = total > 0 ? items[i].value / total * 100.0 : 0.0;

    qsort(items, nitems, sizeof(Item), cmp_items);
    for (i = 0; i < nitems; i++) {
        items[i].rank = i + 1;
        for (j = 0; j < i; j++)
            if (items[j].value == items[i].value) { items[i].rank = items[j].rank; break; }
    }

    char t[32];
    fmt_double(t, sizeof t, total);
    emit("\"items\":[");
    for (i = 0; i < nitems; i++) {
        char b[32];
        emit("{\"label\":");
        emit_label_json(items[i].label);
        emit(",\"value\":");
        fmt_double(b, sizeof b, items[i].value);
        emit(b);
        emit(",\"pct\":");
        fmt_double(b, sizeof b, items[i].pct);
        emit(b);
        emit(",\"rank\":");
        snprintf(b, sizeof b, "%d", items[i].rank);
        emit(b);
        emit("}");
        if (i + 1 < nitems) emit(",");
    }
    emit("]");
}

static int cmp_d(const void *a, const void *b)
{
    double x = *(const double *)a - *(const double *)b;
    return x > 0 ? 1 : x < 0 ? -1 : 0;
}

static void analyze_scores(void)
{
    int i;
    double mean = 0, min = 0, max = 0, median = 0;

    if (nscores == 0) return;
    qsort(scores, nscores, sizeof(double), cmp_d);
    for (i = 0; i < nscores; i++) {
        mean += scores[i];
        if (i == 0 || scores[i] < min) min = scores[i];
        if (i == 0 || scores[i] > max) max = scores[i];
    }
    mean /= nscores;
    median = nscores % 2
        ? scores[nscores / 2]
        : (scores[nscores / 2 - 1] + scores[nscores / 2]) / 2.0;

    char b[32];
    emit("\"scores\":{\"mean\":");
    fmt_double(b, sizeof b, mean);
    emit(b);
    emit(",\"min\":");
    fmt_double(b, sizeof b, min);
    emit(b);
    emit(",\"max\":");
    fmt_double(b, sizeof b, max);
    emit(b);
    emit(",\"median\":");
    fmt_double(b, sizeof b, median);
    emit(b);
    emit("}");
}

int nseries_computed(void) { return nxs > 0; }

static void emit_label_json(const char *s)
{
    const char *p;
    char c2[2] = {0, 0};
    emit("\"");
    for (p = s; *p; p++) {
        if (*p == '"' || *p == '\\') { emit("\\"); c2[0] = *p; emit(c2); }
        else { c2[0] = *p; emit(c2); }
    }
    emit("\"");
}

int main(void)
{
    char line[512];
    int section = 0; /* 0 none, 1 series, 2 items, 3 scores */

    while (fgets(line, sizeof line, stdin)) {
        trim(line);
        if (line[0] == '\0') continue;
        if (line[0] == '#') {
            if (strncmp(line, "#series", 7) == 0) section = 1;
            else if (strncmp(line, "#items", 6) == 0) section = 2;
            else if (strncmp(line, "#scores", 7) == 0) section = 3;
            else section = 0;
            continue;
        }
        if (section == 1) {
            char *comma = strchr(line, ',');
            if (!comma) continue;
            *comma = '\0';
            char *val = comma + 1;
            trim(val);
            double v = atof(val);
            if (nxs < MAX_ROWS) xs[nxs++] = v;
            if (nma < MAX_ROWS) {
                snprintf(ma_pts[nma].label, MAX_LEN, "%.*s", MAX_LEN-1, line);
                ma_pts[nma].value = v;
                nma++;
            }
        } else if (section == 2) {
            char *comma = strchr(line, ',');
            if (!comma) continue;
            *comma = '\0';
            char *val = comma + 1;
            trim(val);
            if (nitems < MAX_ROWS) {
                snprintf(items[nitems].label, MAX_LEN, "%.*s", MAX_LEN-1, line);
                items[nitems].value = atof(val);
                items[nitems].rank = 0;
                items[nitems].pct = 0;
                nitems++;
            }
        } else if (section == 3) {
            if (nscores < MAX_ROWS) scores[nscores++] = atof(line);
        }
    }

    emit("{");
    int first = 1;
    if (nxs > 0) {
        analyze_series();
        first = 0;
    }
    if (nitems > 0) {
        if (!first) emit(",");
        analyze_items();
        first = 0;
    }
    if (nscores > 0) {
        if (!first) emit(",");
        analyze_scores();
    }
    emit("}\n");
    fwrite(out, 1, olen, stdout);
    return 0;
}
