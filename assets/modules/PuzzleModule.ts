import { AppRunner } from "@app/core/runner/AppRunner";
import {AbstractModule} from "@core/module/AbstractModule";
import PuzzleCaptcha from "@lib/ui/PuzzleCaptcha";

export class PuzzleModule extends AbstractModule {
    protected onMount(_runner: AppRunner) {
        if (!customElements.get("puzzle-captcha")) {
            customElements.define("puzzle-captcha", PuzzleCaptcha);
        }
    }
}