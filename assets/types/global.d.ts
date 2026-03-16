export  {}

export interface LmkConfig {
    userId: number;
    roles: string[];
    isPremium: boolean;
    isLogged: boolean;
    preferredTheme: string | null;
    language: "fr" | "en" | null;
}