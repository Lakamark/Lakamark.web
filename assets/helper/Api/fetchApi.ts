export interface FetchApiResult<T = unknown> {
    ok: boolean;
    status: number;
    data: T | null;
    error: string | null;
}

/**
 * Send an HTTP request and safely parse a JSON response.
 */
export async function fetchApi<T = unknown>(
    url: string,
    options: RequestInit = {},
    timeout = 8000
): Promise<FetchApiResult<T> | null> {
    const controller = new AbortController();
    const timer = window.setTimeout(() => controller.abort(), timeout);

    try {
        const headers = new Headers(options.headers ?? {});

        // We return the default contentType,
        // if options.headers is not defined.
        if (!headers.has("Content-Type") && options.body) {
            headers.set("Content-Type", "application/json");
        }

        const response = await fetch(url, {
            ...options,
            headers,
            signal: controller.signal,
        });

        let data: T | null = null;
        let error: string | null = null;

        const contentType = response.headers.get("Content-Type") ?? "";

        if (contentType.includes("application/json")) {
            try {
                data = (await response.json()) as T;
            } catch {
                error = "invalid_json_response";
            }
        }

        if (!response.ok) {
            if (!error && data && typeof data === "object" && "error" in data) {
                const apiError = (data as { error?: string }).error;
                error = apiError ?? "request_failed";
            }

            return {
                ok: false,
                status: response.status,
                data,
                error: error ?? "request_failed",
            };
        }

        return {
            ok: true,
            status: response.status,
            data,
            error,
        }
    } catch (error) {
        console.error("fetchApi error:", error);

        const isAbortError =
            error instanceof DOMException && error.name === "AbortError";

        return {
            ok: false,
            status: 0,
            data: null,
            error: isAbortError ? "request_timeout" : "network_error",
        };
    } finally {
        window.clearTimeout(timer)
    }
}