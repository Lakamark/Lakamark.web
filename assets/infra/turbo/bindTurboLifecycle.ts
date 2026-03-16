export interface TurboLifecycleHandlers {
    onLoad: () => void;
    onBeforeRender: () => void;
    onBeforeCache: () => void;
}

export interface TurboLifecycleBinding {
    destroy(): void;
}

export function bindTurboLifecycle(
    handlers: TurboLifecycleHandlers,
    doc: Document = document
): TurboLifecycleBinding {
    const handleLoad = (): void => {
        handlers.onLoad();
    };

    const handleBeforeRender = (): void => {
        handlers.onBeforeRender();
    };

    const handleBeforeCache = (): void => {
        handlers.onBeforeCache();
    };

    doc.addEventListener("turbo:load", handleLoad);
    doc.addEventListener("turbo:before-render", handleBeforeRender);
    doc.addEventListener("turbo:before-cache", handleBeforeCache);

    return {
        destroy(): void {
            doc.removeEventListener("turbo:load", handleLoad);
            doc.removeEventListener("turbo:before-render", handleBeforeRender);
            doc.removeEventListener("turbo:before-cache", handleBeforeCache);
        }
    };
}