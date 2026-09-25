#!/usr/bin/env bash
# ==============================================================================
# Multi-Agent Workflow Launcher (Antigravity, Claude Code, Aider)
# ==============================================================================

set -e

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

# Ensure Python 3 and Scripts are in PATH on Windows/Git Bash
for p in "$USERPROFILE/AppData/Local/Programs/Python/Python312" "$LOCALAPPDATA/Programs/Python/Python312" "/c/Users/TK ABA SBY 69 (3)/AppData/Local/Programs/Python/Python312"; do
    if [ -d "$p/Scripts" ]; then
        export PATH="$p/Scripts:$p:$PATH"
        break
    fi
done

# Load .env.agents if present
load_env() {
    if [ -f "$PROJECT_ROOT/.env.agents" ]; then
        echo "[*] Loading environment variables from .env.agents..."
        set -a
        # shellcheck disable=SC1091
        source "$PROJECT_ROOT/.env.agents"
        set +a
    else
        echo "[!] Note: .env.agents not found. Create one from .env.agents.example to store API keys."
    fi
}

check_env() {
    echo "=========================================================="
    echo "        MULTI-AGENT ENVIRONMENT & RUNTIME CHECK           "
    echo "=========================================================="

    if command -v git >/dev/null 2>&1; then
        echo "[OK] Git: $(git --version)"
    else
        echo "[ERR] Git not found!"
    fi

    if command -v node >/dev/null 2>&1; then
        echo "[OK] Node.js: $(node -v)"
    else
        echo "[ERR] Node.js not found!"
    fi

    if command -v claude >/dev/null 2>&1; then
        echo "[OK] Claude Code: $(claude --version)"
    else
        echo "[ERR] Claude Code not found! (npm install -g @anthropic-ai/claude-code)"
    fi

    py_ver=""
    if python --version >/dev/null 2>&1; then
        py_ver="$(python --version 2>&1)"
    elif python3 --version >/dev/null 2>&1; then
        py_ver="$(python3 --version 2>&1)"
    elif py -3 --version >/dev/null 2>&1; then
        py_ver="$(py -3 --version 2>&1)"
    fi

    if [[ "$py_ver" =~ ^Python ]]; then
        echo "[OK] Python: $py_ver"
    else
        echo "[ERR] Python 3 not found!"
    fi

    if command -v aider >/dev/null 2>&1; then
        echo "[OK] Aider: $(aider --version)"
    else
        echo "[ERR] Aider not found! (pip install aider-chat)"
    fi

    echo ""
    echo "--- API Credentials Status ---"
    for key in ANTHROPIC_API_KEY DEEPSEEK_API_KEY OPENAI_API_KEY GEMINI_API_KEY; do
        val="${!key}"
        if [ -n "$val" ]; then
            masked="${val:0:7}..."
            echo "  [OK] $key is SET ($masked)"
        else
            echo "  [--] $key is NOT set"
        fi
    done
    echo ""
}

start_claude() {
    load_env
    echo ""
    echo "[>>>] Launching Claude Code Autonomous Session..."
    claude "$@"
}

start_aider() {
    load_env
    model="${AIDER_DEFAULT_MODEL:-sonnet}"
    if [ -n "$1" ]; then
        model="$1"
        shift
    fi

    model_flag="--sonnet"
    case "$model" in
        sonnet) model_flag="--sonnet" ;;
        opus) model_flag="--opus" ;;
        deepseek) model_flag="--model deepseek/deepseek-coder" ;;
        4o) model_flag="--4o" ;;
        gemini) model_flag="--model gemini/gemini-2.5-pro" ;;
        *) model_flag="--model $model" ;;
    esac

    echo ""
    echo "[>>>] Launching Aider Pair Programming Session (Model: $model)..."
    aider $model_flag "$@"
}

# Argument parsing
case "$1" in
    --check|-c)
        load_env
        check_env
        exit 0
        ;;
    --claude)
        shift
        start_claude "$@"
        exit 0
        ;;
    --aider)
        shift
        start_aider "$@"
        exit 0
        ;;
    *)
        load_env
        echo "=========================================================="
        echo "          MULTI-AGENT SESSION CONTROLLER                  "
        echo "=========================================================="
        echo "1. Launch Claude Code (Deep Autonomous Implementer)"
        echo "2. Launch Aider (Precision Pair Programmer - Sonnet)"
        echo "3. Launch Aider (DeepSeek Coder)"
        echo "4. Launch Aider (OpenAI GPT-4o)"
        echo "5. Run Environment & Credential Check"
        echo "Q. Quit"
        echo ""
        read -p "Select option (1-5, Q): " choice
        case "$choice" in
            1) start_claude ;;
            2) start_aider "sonnet" ;;
            3) start_aider "deepseek" ;;
            4) start_aider "4o" ;;
            5) check_env ;;
            *) echo "Exited." ;;
        esac
        ;;
esac
