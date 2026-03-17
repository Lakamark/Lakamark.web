import { defineConfig } from "vite";
import preact from "@preact/preset-vite";
import mkcert from "vite-plugin-mkcert";
import tsconfigPaths from "vite-tsconfig-paths";
import { resolve, dirname } from "node:path";
import { fileURLToPath } from "node:url";
import { twigRefreshPlugin } from "./vite/plugins/twigRefreshPlugin";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

export default defineConfig({
  root: "assets",
  base: "/assets/",
  plugins: [
    tsconfigPaths(),
    preact(),
    mkcert(),
    twigRefreshPlugin(),
  ],
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
    outDir: resolve(__dirname, "public/assets"),
    emptyOutDir: true,
    rollupOptions: {
      input: {
        app: resolve(__dirname, "assets/app.ts"),
        dashboard: resolve(__dirname, "assets/dashboard.ts"),
      },
      output: {
        manualChunks: undefined,
      },
    },
  },
});