import {isThemeName } from "../lib/theme";
import {LmkConfig} from "../types/global";

const DEFAULT_CONFIG  = Object.freeze({
    userId: null,
    roles: [],
    isPremium: false,
    isLogged: false,
    preferredTheme: null
})

let cachedConfig: Readonly<LmkConfig> | null = null;

export function getLmkConfigSafe(): Readonly<LmkConfig> {
    if (cachedConfig) return cachedConfig;

    const c = window.LmkConfig;

    cachedConfig = !c || typeof c !== "object"
        ? DEFAULT_CONFIG
        : Object.freeze({
            userId: typeof c.userId === "number" ? c.userId : null,
            roles: Array.isArray(c.roles) ? c.roles.map(String) : [],
            isPremium: Boolean(c.isPremium),
            isLogged: Boolean(c.isLogged),
            preferredTheme: isThemeName(c.preferredTheme)
                ? c.preferredTheme
                : null,
        });

    return cachedConfig;
}