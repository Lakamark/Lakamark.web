import {AppLanguage} from "./AppLanguage";

export interface AppConfigInterface {
    userId: string | number | null;
    roles: string[];
    isPremium: boolean;
    isLogged: boolean;
    preferredTheme: string | null;
    language: AppLanguage;
}