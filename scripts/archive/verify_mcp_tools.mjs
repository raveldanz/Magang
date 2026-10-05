/**
 * Test & Verification Script for Sub-Agents MCP Tools
 * Validates JSON-RPC 2.0 handshake and executes tool calls for both Claude and Qwen.
 */

import { spawn } from "child_process";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, "..");

console.log("=================================================================");
console.log("   VERIFIKASI INTEGRASI MCP TOOLS: SUB-AGENTS (CLAUDE & QWEN)   ");
console.log("=================================================================\n");

const serverProcess = spawn("node", ["scripts/mcp-agents-server.mjs"], {
  cwd: projectRoot,
  stdio: ["pipe", "pipe", "pipe"],
});

let buffer = "";
let qwenResult = null;
let claudeResult = null;

serverProcess.stderr.on("data", (data) => {
  const msg = data.toString().trim();
  if (msg) {
    console.log(`[MCP Server Log]: ${msg}`);
  }
});

function sendRpc(msg) {
  serverProcess.stdin.write(JSON.stringify(msg) + "\n");
}

async function runTest() {
  return new Promise((resolve, reject) => {
    const timeout = setTimeout(() => {
      serverProcess.kill();
      reject(new Error("Timeout verifikasi MCP Server (melebihi 180 detik)"));
    }, 180000);

    serverProcess.stdout.on("data", (chunk) => {
      buffer += chunk.toString("utf8");
      const lines = buffer.split("\n");
      buffer = lines.pop();

      for (const line of lines) {
        if (!line.trim()) continue;
        try {
          const res = JSON.parse(line.trim());

          // Step 1: Handshake Initialize
          if (res.id === 1) {
            console.log("[1/4] Handshake 'initialize' Sukses:", res.result.serverInfo);
            sendRpc({
              jsonrpc: "2.0",
              method: "notifications/initialized",
            });
            // Step 2: List Tools
            sendRpc({
              jsonrpc: "2.0",
              id: 2,
              method: "tools/list",
              params: {},
            });
          }

          // Step 2 Response: Tools Discovered
          else if (res.id === 2) {
            const toolNames = res.result.tools.map((t) => t.name);
            console.log("[2/4] Tool Discovery Sukses:", toolNames.join(", "));

            // Step 3: Panggil delegate_to_qwen
            console.log("\n[3/4] Menguji tool call 'delegate_to_qwen' (Ollama Local)...");
            sendRpc({
              jsonrpc: "2.0",
              id: 3,
              method: "tools/call",
              params: {
                name: "delegate_to_qwen",
                arguments: {
                  prompt: "Tuliskan kode fungsi PHP format Rupiah tanpa penjelasan: function formatRupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }",
                },
              },
            });
          }

          // Step 3 Response: Qwen Result
          else if (res.id === 3) {
            qwenResult = res.result.content[0].text;
            console.log("--- Output Qwen 2.5 Coder 7B ---");
            console.log(qwenResult.slice(0, 300) + (qwenResult.length > 300 ? "..." : ""));
            console.log("--------------------------------\n");

            // Step 4: Panggil delegate_to_claude
            console.log("[4/4] Menguji tool call 'delegate_to_claude' (Claude Code CLI)...");
            sendRpc({
              jsonrpc: "2.0",
              id: 4,
              method: "tools/call",
              params: {
                name: "delegate_to_claude",
                arguments: {
                  prompt: "Dalam 2 kalimat singkat, apa manfaat pemisahan Service Layer dari Controller di Laravel?",
                },
              },
            });
          }


          // Step 4 Response: Claude Result
          else if (res.id === 4) {
            claudeResult = res.result.content[0].text;
            console.log("--- Output Claude Code CLI ---");
            console.log(claudeResult.slice(0, 300) + (claudeResult.length > 300 ? "..." : ""));
            console.log("------------------------------\n");

            clearTimeout(timeout);
            serverProcess.kill();
            resolve({ qwenResult, claudeResult });
          }
        } catch (e) {
          console.error("Non-JSON output:", line);
        }
      }
    });

    serverProcess.on("error", (err) => {
      clearTimeout(timeout);
      reject(err);
    });
  });
}

// Mulai inisialisasi handshake
sendRpc({
  jsonrpc: "2.0",
  id: 1,
  method: "initialize",
  params: {
    protocolVersion: "2024-11-05",
    capabilities: {},
    clientInfo: {
      name: "mcp-verifier",
      version: "1.0.0",
    },
  },
});

runTest()
  .then(() => {
    console.log("=================================================================");
    console.log(" [HASIL] KEDUA SUB-AGENTS BERHASIL TERVERIFIKASI SEBAGAI MCP TOOLS");
    console.log("=================================================================");
    process.exit(0);
  })
  .catch((err) => {
    console.error("Verifikasi Gagal:", err.message);
    process.exit(1);
  });
