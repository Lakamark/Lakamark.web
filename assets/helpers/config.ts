import type { LmkConfig } from "../types/global";
import { isThemeName } from "../ui/theme/isThemeName";

const DEFAULT_CONFIG: Readonly<LmkConfig> = Object.freeze({
    userId: null,
    roles: [],
    isPremium: false,
    isLogged: false,
    preferredTheme: null,
    language: null
});

let cachedConfig: Readonly<LmkConfig> | null = null;

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === "object" && value !== null;
}

function readConfigPayloadFromDom(): unknown {
    const el = document.getElementById("lmk-config");
    if (!el) return null;

    const json = el.textContent?.trim();
    if (!json) return null;

    try {
        return JSON.parse(json);
    } catch {
        return null;
    }
}

function isLanguage(value: unknown): value is "fr" | "en" {
    return value === "fr" || value === "en";
}

function decodeLmkConfig(raw: unknown): LmkConfig | null {
    if (!isRecord(raw)) return null;

    const userId = typeof raw.userId === "number" ? raw.userId : null;
    const roles = Array.isArray(raw.roles)
        ? raw.roles.map(String)
        : [];
    const isPremium = Boolean(raw.isPremium);
    const isLogged = Boolean(raw.isLogged);
    const preferredTheme = isThemeName(raw.preferredTheme)
        ? raw.preferredTheme
        : null;
    const language = isLanguage(raw.language) ? raw.language : 'en'
    return {
        userId,
        roles,
        isPremium,
        isLogged,
        preferredTheme,
        language
    };
}

export function getLmkConfigSafe(): Readonly<LmkConfig> {
    // Read from the cach the config.
    if (cachedConfig) {
        return cachedConfig;
    }

    const decoded = decodeLmkConfig(readConfigPayloadFromDom());
    if (!decoded) {
        // return the default config,
        // if we can decod the JSON
        return DEFAULT_CONFIG;
    }

    cachedConfig = Object.freeze(decoded);
    return cachedConfig;
}

export function resetLmkConfigCache(): void {
    cachedConfig = null;
}