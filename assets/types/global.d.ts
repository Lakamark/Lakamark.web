export  {}

import type { ThemeName } from "../lib/theme";

export interface LmkConfig {
    userId: number;
    roles: string[];
    isPremium: boolean;
    isLogged: boolean;
    preferredTheme: ThemeName | null;
}

declare global {
    interface Window {
        LmkConfig?: LmkConfig;
    }
}