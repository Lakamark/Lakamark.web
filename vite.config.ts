import { defineConfig } from "vite";
import preact from "@preact/preset-vite";
import mkcert from "vite-plugin-mkcert";
import { resolve } from "node:path";
import { fileURLToPath } from "node:url";
import path from "node:path";
import {twigRefreshPlugin} from "./vite/plugins/twigRefreshPlugin";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const root = "./assets";
const outDirRoot = "../public/assets/";

export default defineConfig({
  plugins: [
    preact(),
    mkcert(),
    twigRefreshPlugin(),
  ],
  root,
  base: "/assets",
  resolve: {
    alias: {
      "@": resolve(__dirname, "assets"),
      "@app": resolve(__dirname, "assets/app"),
      "@core": resolve(__dirname, "assets/app/core"),
      "@bootstrap": resolve(__dirname, "assets/bootstrap"),
      "@modules": resolve(__dirname, "assets/modules"),
      "@lib": resolve(__dirname, "assets/lib"),
    },
  },
  server: {
    port: 3000,
    host: "0.0.0.0",
    https: {},
    watch: {
      disableGlobbing: false,
    },
  },
  build: {
    manifest: true,
    assetsDir: "",
    outDir: outDirRoot,
    rollupOptions: {
      output: {
        manualChunks: undefined,
      },
      input: {
        app: resolve(__dirname, "assets/app.ts"),
        dashboard: resolve(__dirname, "assets/dashboard.ts"),
      },
    },
  },
});