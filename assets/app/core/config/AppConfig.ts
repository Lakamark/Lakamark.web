import {AppLanguage} from "@core/config/AppLanguage";
import {AppConfigInterface} from "@core/config/AppConfigInterface";

export class AppConfig {
    public readonly userId: string | number | null;
    public readonly roles: string[];
    public readonly isPremium: boolean;
    public readonly isLogged: boolean;
    public readonly preferredTheme: string | null;
    public readonly language: AppLanguage;

    public constructor(data: AppConfigInterface) {
        this.userId = data.userId;
        this.roles = data.roles;
        this.isPremium = data.isPremium;
        this.isLogged = data.isLogged;
        this.preferredTheme = data.preferredTheme;
        this.language = data.language;
    }

    public hasRole(role: string): boolean {
        return this.roles.includes(role);
    }

    public isAuthenticated(): boolean {
        return this.isLogged;
    }
}