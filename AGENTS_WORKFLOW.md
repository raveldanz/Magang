# Tri-Agent Orchestration Pipeline: Antigravity + Claude Code + Local Qwen

> Master guidelines mengacu pada [AGENTS.md](AGENTS.md) dan aturan arsitektur Laravel di [.antigravity/rules/architecture.md](.antigravity/rules/architecture.md).

## 1. Arsitektur & Pembagian Peran

| Agen | Mesin / Model | Peran Utama | Cara Eksekusi |
| :--- | :--- | :--- | :--- |
| **Gemini Antigravity** | Gemini 3.8 Flash (High) | **Master Orchestrator & QA Gatekeeper**<br>- Analisis arsitektur & alur sistem Laravel<br>- Review kepatuhan SOLID & PSR-12<br>- Integrasi PostgreSQL MCP & E2E Validation<br>- Antarmuka utama instruksi pengguna | Langsung di Antigravity IDE Chat |
| **Claude Code CLI** | Claude 3.7 Sonnet (CLI Session) | **Deep Reasoning & Complex Refactor Specialist**<br>- Penalaran logika bisnis lintas file<br>- Refactoring modul kompleks & Service Layer<br>- Eksekusi CLI non-interaktif berlisensi aktif | `.\agent-runner.ps1 -Agent claude -Prompt "..."` |
| **Qwen 2.5 Coder 7B** | Local Ollama (Offline Engine) | **Fast Isolated Coding & Helper Generator**<br>- Pembuatan fungsi helper, regex, algoritma terisolasi<br>- Template kode dan pengolahan data tanpa batas token<br>- 100% lokal & offline | `.\agent-runner.ps1 -Agent qwen -Prompt "..."` |

---

## 2. Cara Menjalankan Sub-Agent (`agent-runner.ps1`)

Skrip runner universal terletak di root direktori: [`agent-runner.ps1`](agent-runner.ps1).

### Contoh Pemanggilan:

1. **Menjalankan Qwen 2.5 Coder (Lokal/Offline):**
   ```powershell
   .\agent-runner.ps1 -Agent qwen -Prompt "Buat fungsi helper PHP validasi format NIK"
   ```

2. **Menjalankan Claude Code (Non-interaktif):**
   ```powershell
   .\agent-runner.ps1 -Agent claude -Prompt "Audit struktur Controller dan Service pada modul Pengajuan Magang"
   ```

3. **Menyimpan Output Agen ke File:**
   ```powershell
   .\agent-runner.ps1 -Agent qwen -Prompt "Buat template migration Laravel" -OutputFile "scratch/migration_template.php"
   ```

---

## 3. Siklus Kerja Tertutup (*Closed-Loop Protocol*)

1. **Instruksi Masuk**: Diterima dan dianalisis arsitekturnya oleh **Antigravity**.
2. **Delegasi**:
   - Jika butuh penalaran repositori mendalam $\rightarrow$ Delegasikan ke **Claude Code**.
   - Jika butuh fungsi terisolasi/helper cepat $\rightarrow$ Delegasikan ke **Qwen Lokal**.
3. **Penyatuan & Validasi**:
   - Antigravity menyatukan output ke dalam file project secara *surgical edit*.
   - Validasi sintaks PHP (`php -l`), cache clear (`php artisan optimize:clear`), dan automated tests (`php artisan test`).
   - Memastikan **Strict Exit Code 0** sebelum menyerahkan hasil ke pengguna.
