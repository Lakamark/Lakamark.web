import {Theme} from "../../theme";

/**
 * Application configuration injected from Twig.
 *
 * Source:
 * <script id="lmk-config" type="application/json">...</script>
 *
 * This object is parsed once at application startup and then
 * passed to modules.
 */
export interface AppConfig {
    userId: number | null;
    roles: string[];
    isPremium: boolean;
    isLogged: boolean;
    preferredTheme: Theme | null;
    language: 'en' | 'fr';
    environment: 'dev' | 'prod' | 'test';
}