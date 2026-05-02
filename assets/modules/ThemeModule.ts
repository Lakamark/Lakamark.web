import {
    AbstractModule,
    AppContext,
    MODULE_NAMES
} from "../core";
import {
    BodyClassThemeApplier,
    LocalStorageThemeStorage,
    PreferredThemeResolver,
    ThemeManager,
    ThemeSwitcherElement
} from "../theme";

/**
 * Application module responsible for mounting the theme system.
 *
 * This module acts as a bridge between the application lifecycle
 * and the theme domain.
 *
 * Responsibilities:
 * - create the concrete theme dependencies
 * - initialize the ThemeManager on mount
 * - destroy the ThemeManager on unmount
 *
 * This module should stay thin.
 * Theme logic belongs in ThemeManager and its dependencies.
 */
export class ThemeModule extends AbstractModule {
    readonly name = MODULE_NAMES.THEME;

    private manager: ThemeManager | null = null;


    /**
     * Mounts the theme system into the application lifecycle.
     *
     * @param context - Current application context
     */
    protected onMount(context: AppContext): void {
        this.manager = new ThemeManager(
            new BodyClassThemeApplier(),
            new LocalStorageThemeStorage(),
            new PreferredThemeResolver(),
        );

        this.manager.init(context);

        // bind manager to switchers
        document
            .querySelectorAll<ThemeSwitcherElement>('lmk-theme-switcher')
            .forEach((element): void => {
                if (element instanceof ThemeSwitcherElement) {
                    element.setThemeManager(this.manager!);
                }
            });
    }

    /**
     * Destroys the theme system.
     */
    protected onDestroy(): void {
        this.manager?.destroy();
        this.manager = null;
    }
}