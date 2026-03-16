import {PluginOption, ViteDevServer} from "vite";

export function twigRefreshPlugin(): PluginOption {
    return {
        name: "twig-refresh",

        configureServer(server: ViteDevServer) {
            server.watcher.add("templates/**/*.twig");

            server.watcher.on("change", (filePath: string) => {
                if (!filePath.endsWith(".twig")) {
                    return;
                }

                server.ws.send({
                    type: "full-reload",
                });
            });
        },
    };
}