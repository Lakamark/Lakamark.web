import {AbstractInstanceModule} from "@core/module/AbstractInstanceModule";
import {ThemeManager} from "@lib/ThemeManager";
import {AppRunner} from "@core/runner/AppRunner";
import ThemeSwitcherElement from "@/components/ThemeSwitcherElement";

export class ThemeModule extends AbstractInstanceModule<ThemeManager> {
    protected createInstance(_runner: AppRunner): ThemeManager {
        const manager = new ThemeManager();
        manager.boot();

        return manager;
    }

    protected afterInstanceMount(instance: ThemeManager, _runner: AppRunner) {
        ThemeSwitcherElement.manager = instance

        if (!customElements.get("theme-switcher")) {
            customElements.define("theme-switcher", ThemeSwitcherElement);
        }
    }

    protected beforeInstanceDestroy(_instance: ThemeManager | null) {
        ThemeSwitcherElement.manager = null;
    }
}