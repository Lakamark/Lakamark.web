import {AbstractInstanceModule} from "../app/core/module/AbstractInstanceModule";
import {HeaderUI} from "../lib/HeaderUI";
import {AppRunner} from "../app/core/runner/AppRunner";

export class HeaderModule extends AbstractInstanceModule<HeaderUI> {
    protected createInstance(_runner: AppRunner): HeaderUI {
        const headerUI = new HeaderUI();
        return headerUI.init();
    }


    protected onDestroy() {
        this.instance?.destroy()

    }
}