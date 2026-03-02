// assets/ui/theme/isThemeName.ts
import {ThemeName} from "../../elements/ThemeSwitcher";

const THEMES: readonly ThemeName[] = ["day-theme", "night-theme"] as const;

export function isThemeName(value: unknown): value is ThemeName {
    return typeof value === "string" && (THEMES as readonly string[]).includes(value);
}