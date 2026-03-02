export  {}

export interface LmkConfig {
    userId: number;
    roles: string[];
    isPremium: boolean;
    isLogged: boolean;
    preferredTheme: string | null;
}

declare global {
    interface Window {
        LmkConfig?: LmkConfig;
    }
}