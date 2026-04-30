import {AbstractModule} from "../../core";
import {
    ConfigThemeResolver,
    LocalThemeStorage,
    ThemeManager
} from "./core";
import {ThemeModuleOptions} from "./Contracts";
import {AppConfig} from "../../dom";
import {ThemeSwitcherElement} from "./element";

const ELEMENT_NAME = 'lmk-theme-switcher';

/**
 * Application module responsible for mounting the theme system.
 *
 * This module bridges:
 * - AppConfig
 * - ThemeManager
 * - ThemeSwitcherElement
 */
export class ThemeModule extends AbstractModule {
    private manager: ThemeManager | null = null;

    constructor(private readonly options: ThemeModuleOptions) {
        super();
    }

    protected onMount(_config: AppConfig): void {
        super.onMount(_config);

        this.defineCustomElement();

        const storage = new LocalThemeStorage();
        const resolver = new ConfigThemeResolver(this.options.config);

        const initialTheme = resolver.resolve() ?? storage.get() ?? 'day-theme';

        this.manager = new ThemeManager(storage, {
            root: document.body,
            defaultTheme: initialTheme
        });

        this.manager.init();
        this.connectSwitchers();
    }

    protected onDestroy(): void {
        super.onDestroy();

        this.manager = null;
    }

    private defineCustomElement(): void {
        if (!customElements.get(ELEMENT_NAME)) {
            customElements.define(ELEMENT_NAME, ThemeSwitcherElement);
        }
    }

    private connectSwitchers(): void {
        if (!this.manager) {
            return;
        }

        document
            .querySelectorAll<ThemeSwitcherElement>(ELEMENT_NAME)
            .forEach((element: ThemeSwitcherElement): void => {
                element.setThemeManager(this.manager!);
            });
    }
}