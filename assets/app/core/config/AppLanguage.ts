export const APP_LANGUAGES = ["en", "fr"] as const;

export type AppLanguage = typeof APP_LANGUAGES[number];

export const DEFAULT_LANGUAGE: AppLanguage = "en";

export function isAppLanguage(value: unknown): value is AppLanguage {
    return typeof value === "string" && APP_LANGUAGES.includes(value as AppLanguage);
}

export function normalizeLanguage(value: unknown): AppLanguage {
    return isAppLanguage(value) ? value : DEFAULT_LANGUAGE;
}