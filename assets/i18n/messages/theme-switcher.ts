import {TranslationDictionary} from "@/i18n/translate";

export interface ThemeSwitcherMessages {
    switchTheme: string;
    activateLight: string;
    activateDark: string;
}

export const THEME_SWITCHER_MESSAGES: TranslationDictionary<ThemeSwitcherMessages> = {
    en: {
        switchTheme: "Switch theme",
        activateLight: "Activate light theme",
        activateDark: "Activate dark theme",
    },
    fr: {
        switchTheme: "Switch theme",
        activateLight: "Activate light theme",
        activateDark: "Activate dark theme",
    }
}