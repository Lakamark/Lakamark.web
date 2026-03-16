import {AppLanguage} from "@core/config/AppLanguage";

export interface AppConfigInterface {
    userId: string | number | null;
    roles: string[];
    isPremium: boolean;
    isLogged: boolean;
    preferredTheme: string | null;
    language: AppLanguage;
}