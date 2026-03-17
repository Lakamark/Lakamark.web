import {AbstractModule} from "../app/core/module/AbstractModule";
import {AppRunner} from "../app/core/runner/AppRunner";
import PuzzleCaptcha from "../lib/ui/PuzzleCaptcha";

export class PuzzleModule extends AbstractModule {
    protected onMount(_runner: AppRunner) {
        if (!customElements.get("puzzle-captcha")) {
            customElements.define("puzzle-captcha", PuzzleCaptcha);
        }
    }
}