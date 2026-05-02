import {AppContext} from "../contracts";

/**
 * Factory function used to create a fresh AppContext.
 *
 * This is required for environments like Turbo where the DOM
 * is replaced without reloading JavaScript.
 */
export type AppContextFactory = () => AppContext;