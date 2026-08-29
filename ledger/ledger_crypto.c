/**
 * ledger_crypto.c — Tamper-evident hash chain cryptography for Edunex Integrity Ledger
 * 
 * Compiled as shared library, loaded via PHP FFI.
 * Uses OpenSSL's SHA-256 and HMAC-SHA-256 for all operations.
 * 
 * Build: gcc -shared -fPIC -O2 -o ledger_crypto.so ledger_crypto.c -lcrypto
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdint.h>
#include <openssl/sha.h>
#include <openssl/hmac.h>
#include <openssl/evp.h>

#define HASH_LEN 64      /* SHA-256 hex string length (32 bytes * 2) */
#define HMAC_KEY_LEN 32  /* 256-bit HMAC key */

/* ─── Helper: raw bytes → hex string ─── */
static void to_hex(const unsigned char *raw, size_t raw_len, char *hex) {
    for (size_t i = 0; i < raw_len; i++) {
        sprintf(hex + (i * 2), "%02x", raw[i]);
    }
    hex[raw_len * 2] = '\0';
}

/* ─── SHA-256 hash of arbitrary data ─── */
/* Returns hex string in caller-provided buffer (min 65 bytes). */
void ledger_sha256(const char *data, int data_len, char out[65]) {
    unsigned char hash[SHA256_DIGEST_LENGTH];
    SHA256((const unsigned char *)data, data_len, hash);
    to_hex(hash, SHA256_DIGEST_LENGTH, out);
}

/* ─── HMAC-SHA-256 ─── */
void ledger_hmac(const char *key, int key_len, const char *data, int data_len, char out[65]) {
    unsigned char hash[EVP_MAX_MD_SIZE];
    unsigned int hash_len = 0;
    HMAC(EVP_sha256(), key, key_len, (const unsigned char *)data, data_len, hash, &hash_len);
    to_hex(hash, hash_len, out);
}

/* ─── Chain hash: SHA-256(prev_hash + table + record_id + action + data + timestamp) ─── */
void ledger_chain_hash(
    const char *prev_hash,
    const char *table_name,
    const char *record_id,
    const char *action,
    const char *data,
    const char *timestamp,
    char out[65]
) {
    /* Build composite string */
    size_t total = strlen(prev_hash) + strlen(table_name) + strlen(record_id)
                 + strlen(action) + strlen(data) + strlen(timestamp) + 6; /* separators */
    char *buf = (char *)malloc(total);
    if (!buf) { out[0] = '\0'; return; }

    int off = 0;
    off += sprintf(buf + off, "%s|%s|%s|%s|%s|%s", prev_hash, table_name, record_id, action, data, timestamp);

    unsigned char hash[SHA256_DIGEST_LENGTH];
    SHA256((const unsigned char *)buf, off, hash);
    to_hex(hash, SHA256_DIGEST_LENGTH, out);
    free(buf);
}

/* ─── HMAC-signed chain hash ─── */
void ledger_signed_hash(
    const char *hmac_key,
    const char *prev_hash,
    const char *table_name,
    const char *record_id,
    const char *action,
    const char *data,
    const char *timestamp,
    char out[65]
) {
    size_t total = strlen(prev_hash) + strlen(table_name) + strlen(record_id)
                 + strlen(action) + strlen(data) + strlen(timestamp) + 6;
    char *buf = (char *)malloc(total);
    if (!buf) { out[0] = '\0'; return; }

    int off = 0;
    off += sprintf(buf + off, "%s|%s|%s|%s|%s|%s", prev_hash, table_name, record_id, action, data, timestamp);

    unsigned char hash[EVP_MAX_MD_SIZE];
    unsigned int hash_len = 0;
    HMAC(EVP_sha256(), hmac_key, strlen(hmac_key), (const unsigned char *)buf, off, hash, &hash_len);
    to_hex(hash, hash_len, out);
    free(buf);
}

/* ─── Verify a single chain link ─── */
/* Returns 1 if valid, 0 if broken. */
int ledger_verify_link(
    const char *expected_hash,
    const char *prev_hash,
    const char *table_name,
    const char *record_id,
    const char *action,
    const char *data,
    const char *timestamp
) {
    char computed[65];
    ledger_chain_hash(prev_hash, table_name, record_id, action, data, timestamp, computed);
    return strcmp(expected_hash, computed) == 0 ? 1 : 0;
}

/* ─── Generate HMAC key ─── */
void ledger_generate_key(char out[65]) {
    unsigned char raw[32];
    FILE *f = fopen("/dev/urandom", "rb");
    if (f) {
        fread(raw, 1, 32, f);
        fclose(f);
    } else {
        /* Fallback: not truly random but functional */
        for (int i = 0; i < 32; i++) raw[i] = (unsigned char)(rand() & 0xFF);
    }
    to_hex(raw, 32, out);
}

/* ─── Batch verify: returns number of broken links (0 = chain intact) ─── */
int ledger_batch_verify(
    const char **hashes,
    const char **prev_hashes,
    const char **tables,
    const char **record_ids,
    const char **actions,
    const char **data_arr,
    const char **timestamps,
    int count
) {
    int broken = 0;
    for (int i = 0; i < count; i++) {
        if (!ledger_verify_link(hashes[i], prev_hashes[i], tables[i], record_ids[i], actions[i], data_arr[i], timestamps[i])) {
            broken++;
        }
    }
    return broken;
}
