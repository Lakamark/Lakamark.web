export const THEME_NAMES = ["dark", "light"] as const;
export type ThemeName = (typeof THEME_NAMES)[number];

export function isThemeName(v: unknown): v is ThemeName {
    return typeof v === "string" && (THEME_NAMES as readonly string[]).includes(v);
}