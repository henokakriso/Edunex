#!/usr/bin/env bash
# Edunex — AI model setup script.
#
# Edunex talks to a local Ollama server (http://127.0.0.1:11434) and expects
# two models registered under the names:
#   - "edunex-tutor"   (qwen2.5 3B, Q4_K_M)  — text model (required)
#   - "edunex-vision"  (deepseek-vl2-tiny)   — vision model (optional)
#
# This script installs Ollama if needed, downloads the models (multi-GB, which
# is why they are not committed to the repository), and registers them under
# the names Edunex expects.

set -euo pipefail

MODELS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/model"
TUTOR_GGUF="$MODELS_DIR/qwen2.5-3b-instruct-q4_k_m.gguf"
VISION_DIR="$MODELS_DIR/deepseek-vl2-tiny"

info()  { printf '\033[1;34m[edunex-ai]\033[0m %s\n' "$*"; }
warn()  { printf '\033[1;33m[edunex-ai]\033[0m %s\n' "$*"; }

# ---- 1. Ollama binary -------------------------------------------------------
if ! command -v ollama >/dev/null 2>&1; then
    warn "Ollama is not installed. Installing it now (needs sudo)…"
    curl -fsSL https://ollama.com/install.sh | sudo sh
fi

info "Ollama $(ollama --version)"

# ---- 2. Ollama server -------------------------------------------------------
if ! curl -fsS --max-time 3 http://127.0.0.1:11434/api/version >/dev/null 2>&1; then
    warn "Ollama server is not running — starting it…"
    if command -v systemctl >/dev/null 2>&1; then
        sudo systemctl start ollama 2>/dev/null || nohup ollama serve >/dev/null 2>&1 &
    else
        nohup ollama serve >/dev/null 2>&1 &
    fi
    for i in $(seq 1 30); do
        curl -fsS --max-time 2 http://127.0.0.1:11434/api/version >/dev/null 2>&1 && break
        sleep 1
    done
fi
curl -fsS http://127.0.0.1:11434/api/version >/dev/null || { warn "Ollama server did not start. Run 'ollama serve' manually."; exit 1; }
info "Ollama server is up."

# ---- 3. Text model => "edunex-tutor" ----------------------------------------
register_from_file() {
    local name="$1" path="$2"
    local modelfile
    modelfile="$(mktemp)"
    printf 'FROM %s\n' "$path" > "$modelfile"
    ollama create "$name" -f "$modelfile" >/dev/null
    rm -f "$modelfile"
    info "Registered '$name' from $path"
}

if [ -f "$TUTOR_GGUF" ]; then
    [ "$(ollama list --format json 2>/dev/null | grep -c '"edunex-tutor"' || true)" -eq 0 ] \
        && register_from_file edunex-tutor "$TUTOR_GGUF" \
        || info "'edunex-tutor' already registered."
else
    info "Downloading qwen2.5:3b (text model, ~2GB)…"
    ollama pull qwen2.5:3b >/dev/null
    if ! ollama cp qwen2.5:3b edunex-tutor >/dev/null 2>&1; then
        register_from_file edunex-tutor qwen2.5:3b
    fi
    info "'edunex-tutor' ready (alias of qwen2.5:3b)."
fi

# ---- 4. Vision model => "edunex-vision" (optional) ---------------------------
if [ -d "$VISION_DIR" ]; then
    [ "$(ollama list --format json 2>/dev/null | grep -c '"edunex-vision"' || true)" -eq 0 ] \
        && register_from_file edunex-vision "$VISION_DIR" \
        || info "'edunex-vision' already registered."
else
    warn "DeepSeek-VL2 model folder not found at $VISION_DIR"
    warn "Skipping vision model (optional). Place the model folder there and re-run, or:"
    warn "  ollama pull deepseek-vl2 && ollama cp deepseek-vl2 edunex-vision"
fi

# ---- 5. Done -----------------------------------------------------------------
info "Registered models:"
ollama list
info "Done — restart Edunex AI workers or refresh Settings → AI & Learning."