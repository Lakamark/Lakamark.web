import {
    AppConfigInterface,
    AppLanguage
} from "../config/index";


/**
 * Immutable representation of the validated frontend application configuration.
 *
 * This object is created after the configuration has been:
 * - parsed from the DOM
 * - validated and normalized by the config parser
 *
 * It acts as a reliable source of truth for the frontend runtime.
 *
 * This class should only receive already validated data.
 * No validation logic should be implemented here.
 */
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

    /**
     * Checks whether the current user has a specific role.
     *
     * Roles are provided by the backend and are used to:
     * - control access to frontend features
     * - determine which API resources can be requested
     *
     * @param role - Role identifier to check (e.g. "ROLE_ADMIN")
     *
     * @returns `true` if the user has the given role, otherwise `false`
     */
    public hasRole(role: string): boolean {
        return this.roles.includes(role);
    }

    /**
     * Indicates whether the user is authenticated.
     *
     * This value is derived from the backend session state and can be used
     * to guard frontend logic before executing authenticated actions.
     *
     * @returns `true` if the user is logged in, otherwise `false`
     */
    public isAuthenticated(): boolean {
        return this.isLogged;
    }
}