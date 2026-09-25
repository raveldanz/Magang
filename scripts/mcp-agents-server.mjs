#!/usr/bin/env node
/**
 * MCP Server Bridge for Sub-Agents (Claude Code & Ollama Qwen 2.5 Coder 7B)
 * Implements Model Context Protocol (STDIO Transport) for Antigravity IDE.
 */

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import { spawn } from "child_process";
import path from "path";
import { fileURLToPath } from "url";
import fs from "fs";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, "..");

// 1. Muat .env.agents jika tersedia untuk memastikan API key aktif
const envAgentsPath = path.join(projectRoot, ".env.agents");
if (fs.existsSync(envAgentsPath)) {
  try {
    const envContent = fs.readFileSync(envAgentsPath, "utf-8");
    for (const line of envContent.split(/\r?\n/)) {
      const trimmed = line.trim();
      if (trimmed && !trimmed.startsWith("#")) {
        const match = trimmed.match(/^([^=]+)=(.*)$/);
        if (match) {
          const key = match[1].trim();
          const val = match[2].trim();
          // Catatan penting: Jika ANTHROPIC_API_KEY bukan format resmi sk-ant-,
          // jangan overwrite env agar Claude Code menggunakan sesi login resminya.
          if (key === "ANTHROPIC_API_KEY" && !val.startsWith("sk-ant-")) {
            continue;
          }
          if (!process.env[key] && val) {
            process.env[key] = val;
          }
        }
      }
    }
  } catch (err) {
    console.error("[mcp-agents-server] Peringatan membaca .env.agents:", err.message);
  }
}


// 2. Pastikan binary path penting (npm, ollama, python) ada di PATH
const extraPaths = [
  path.join(process.env.APPDATA || "", "npm"),
  path.join(process.env.LOCALAPPDATA || "", "Programs", "Ollama"),
  path.join(process.env.LOCALAPPDATA || "", "Programs", "Python", "Python312"),
  path.join(process.env.LOCALAPPDATA || "", "Programs", "Python", "Python312", "Scripts"),
];

for (const ep of extraPaths) {
  if (fs.existsSync(ep) && !process.env.PATH.includes(ep)) {
    process.env.PATH = `${ep};${process.env.PATH}`;
  }
}

/**
 * Eksekusi Claude Code CLI non-interaktif
 */
function executeClaude(prompt) {
  return new Promise((resolve, reject) => {
    const isWin = process.platform === "win32";
    const cmd = isWin ? "cmd.exe" : "claude";
    const args = isWin
      ? ["/d", "/s", "/c", "claude", "-p", prompt]
      : ["-p", prompt];

    const child = spawn(cmd, args, {
      cwd: projectRoot,
      stdio: ["ignore", "pipe", "pipe"],
      env: { ...process.env },
    });

    let stdout = "";
    let stderr = "";

    child.stdout.on("data", (chunk) => {
      stdout += chunk.toString("utf8");
    });

    child.stderr.on("data", (chunk) => {
      stderr += chunk.toString("utf8");
    });

    child.on("error", (err) => {
      reject(err);
    });

    child.on("close", (code) => {
      if (stdout.trim()) {
        resolve(stdout.trim());
      } else if (stderr.trim()) {
        resolve(`[Claude stderr]:\n${stderr.trim()}`);
      } else {
        resolve(`[Claude selesai dengan exit code: ${code}]`);
      }
    });
  });
}

/**
 * Eksekusi Ollama Qwen 2.5 Coder 7B (Fast HTTP API dengan Fallback CLI)
 */
async function executeQwen(prompt) {
  // Metode 1: HTTP API lokal Ollama (cepat, stabil, teks bersih tanpa ANSI/braille spinner)
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 180000); // 3 menit timeout untuk toleransi CPU inference


    const res = await fetch("http://127.0.0.1:11434/api/generate", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        model: "qwen2.5-coder:7b",
        prompt: prompt,
        stream: false,
      }),
      signal: controller.signal,
    });
    clearTimeout(timeoutId);

    if (res.ok) {
      const data = await res.json();
      if (data && data.response) {
        return data.response.trim();
      }
    }
  } catch (httpErr) {
    console.error("[mcp-agents-server] HTTP API fallback ke CLI:", httpErr.message);
  }

  // Metode 2: CLI Stdio Fallback ($prompt | ollama run qwen2.5-coder:7b)
  return new Promise((resolve, reject) => {
    const isWin = process.platform === "win32";
    const cmd = isWin ? "cmd.exe" : "ollama";
    const args = isWin
      ? ["/d", "/s", "/c", "ollama", "run", "qwen2.5-coder:7b"]
      : ["run", "qwen2.5-coder:7b"];

    const child = spawn(cmd, args, {
      cwd: projectRoot,
      stdio: ["pipe", "pipe", "pipe"],
      env: { ...process.env },
    });

    let stdout = "";
    let stderr = "";

    child.stdout.on("data", (chunk) => {
      stdout += chunk.toString("utf8");
    });

    child.stderr.on("data", (chunk) => {
      stderr += chunk.toString("utf8");
    });

    child.on("error", (err) => {
      reject(err);
    });

    child.on("close", (code) => {
      // Bersihkan karakter spinner braille dan ANSI escape codes
      const cleanStdout = stdout
        .replace(/[\u2800-\u28FF]/g, "")
        .replace(/\x1b\[[0-9;]*[a-zA-Z]/g, "")
        .trim();

      if (cleanStdout) {
        resolve(cleanStdout);
      } else if (stderr.trim()) {
        const cleanStderr = stderr
          .replace(/[\u2800-\u28FF]/g, "")
          .replace(/\x1b\[[0-9;]*[a-zA-Z]/g, "")
          .trim();
        resolve(`[Qwen stderr]:\n${cleanStderr}`);
      } else {
        resolve(`[Qwen selesai dengan exit code: ${code}]`);
      }
    });

    child.stdin.write(prompt);
    child.stdin.end();
  });
}

// 3. Konfigurasi MCP Server
const server = new Server(
  {
    name: "subagents",
    version: "1.0.0",
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

// 4. Daftarkan Tools ke MCP
server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: [
      {
        name: "delegate_to_claude",
        description:
          "Mendelegasikan tugas penalaran tingkat tinggi, analisis arsitektur repositori, audit multi-file, dan refactoring menyeluruh kepada Claude Code CLI non-interaktif.",
        inputSchema: {
          type: "object",
          properties: {
            prompt: {
              type: "string",
              description:
                "Instruksi atau pertanyaan mendalam untuk Claude Code CLI mengenai arsitektur, bug analysis, atau review kode.",
            },
          },
          required: ["prompt"],
        },
      },
      {
        name: "delegate_to_qwen",
        description:
          "Mendelegasikan tugas pembuatan fungsi modular, pembuatan helper class, unit testing, dan algoritma lokal offline kepada Ollama Qwen 2.5 Coder 7B secara non-interaktif tanpa biaya token.",
        inputSchema: {
          type: "object",
          properties: {
            prompt: {
              type: "string",
              description:
                "Instruksi pembuatan fungsi helper, algoritma spesifik, atau pengujian unit lokal untuk Qwen 2.5 Coder 7B.",
            },
          },
          required: ["prompt"],
        },
      },
    ],
  };
});

// 5. Handler Eksekusi Tool
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;
  const prompt = args?.prompt || "";

  if (!prompt) {
    return {
      content: [
        {
          type: "text",
          text: "Error: Parameter 'prompt' wajib diisi untuk sub-agent.",
        },
      ],
      isError: true,
    };
  }

  if (name === "delegate_to_claude") {
    try {
      const result = await executeClaude(prompt);
      return {
        content: [
          {
            type: "text",
            text: result,
          },
        ],
        isError: false,
      };
    } catch (err) {
      return {
        content: [
          {
            type: "text",
            text: `[Error delegate_to_claude]: ${err.message}`,
          },
        ],
        isError: true,
      };
    }
  }

  if (name === "delegate_to_qwen") {
    try {
      const result = await executeQwen(prompt);
      return {
        content: [
          {
            type: "text",
            text: result,
          },
        ],
        isError: false,
      };
    } catch (err) {
      return {
        content: [
          {
            type: "text",
            text: `[Error delegate_to_qwen]: ${err.message}`,
          },
        ],
        isError: true,
      };
    }
  }

  throw new Error(`Tool tidak dikenal: ${name}`);
});

// 6. Jalankan Server via STDIO Transport
async function run() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error("[mcp-agents-server] Sub-agents MCP Server aktif pada STDIO transport.");
}

run().catch((err) => {
  console.error("[mcp-agents-server] Fatal error:", err);
  process.exit(1);
});
